<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Factory\ActivityPub\ActivityFactory;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Message\ActivityPub\Outbox\LikeMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\LikeWrapper;
use App\Service\ActivityPub\Wrapper\UndoWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class LikeHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LikeWrapper $likeWrapper,
        private readonly UndoWrapper $undoWrapper,
        private readonly ActivityPubManager $activityPubManager,
        private readonly ActivityFactory $activityFactory,
        private readonly MessageBusInterface $bus,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    #[ArrayShape([
        '@context' => 'string',
        'id' => 'string',
        'actor' => 'string',
        'object' => 'string',
    ])]
    public function __invoke(
        LikeMessage $message
    ): void {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $user = $this->userRepository->find($message->userId);
        $object = $this->entityManager->getRepository($message->objectType)->find($message->objectId);

        $activity = $this->likeWrapper->build(
            $this->activityPubManager->getActorProfileId($user),
            $this->activityFactory->create($object),
        );

        if ($message->removeLike) {
            $activity = $this->undoWrapper->build($activity);
        }

        $this->deliver($this->userRepository->findAudience($user), $activity);
        $this->deliver($this->magazineRepository->findAudience($object->magazine), $activity);
    }

    private function deliver(array $followers, array $activity): void
    {
        foreach ($followers as $follower) {
            if (is_string($follower)) {
                $this->bus->dispatch(new DeliverMessage($follower, $activity));
                continue;
            }

            if($follower->apInboxUrl) {
                $this->bus->dispatch(new DeliverMessage($follower->apInboxUrl, $activity));
            }
        }
    }
}
