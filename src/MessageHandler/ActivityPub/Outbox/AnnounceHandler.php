<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\User;
use App\Factory\ActivityPub\ActivityFactory;
use App\Message\ActivityPub\Outbox\AnnounceMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\AnnounceWrapper;
use App\Service\ActivityPubManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class AnnounceHandler implements MessageHandlerInterface
{
    public function __construct(
        private UserRepository $repository,
        private EntityManagerInterface $entityManager,
        private AnnounceWrapper $announceWrapper,
        private ActivityPubManager $activityPubManager,
        private ActivityFactory $activityFactory,
        private MessageBusInterface $bus,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[ArrayShape(['@context' => "string", 'id' => "string", 'actor' => "string", 'object' => "string"])] public function __invoke(
        AnnounceMessage $message
    ): void {
        $id = Uuid::v4()->toRfc4122(); // todo save ap event stream

        $user = $this->repository->find($message->userId);
        $object = $this->entityManager->getRepository($message->objectType)->find($message->objectId);

        $activity = $this->announceWrapper->build(
            $this->activityPubManager->getActorProfileId($user),
            $this->activityFactory->create($object),
            $message->createdAt
        );

        $followers = $this->repository->findAudience($user);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }

        $followers = $this->getFollowersFromObject($activity, $user);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower, $activity));
        }
    }

    public function getFollowersFromObject(array $activity, User $user): array
    {
        if (isset($activity['cc']) && isset($activity['to'])) {
            $followersUrl = $this->urlGenerator->generate(
                'ap_user_followers',
                ['username' => $user->username],
                UrlGeneratorInterface::ABS_URL
            );

            return array_unique(
                array_filter(
                    array_merge(
                        is_array($activity['cc']) ? $activity['cc'] : [$activity['cc']],
                        is_array($activity['to']) ? $activity['to'] : [$activity['to']]
                    ), fn($val) => !in_array($val, [ActivityPubActivityInterface::PUBLIC_URL, $followersUrl])
                )
            );
        }

        return [];
    }
}
