<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\CreateWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateHandler implements MessageHandlerInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private UserRepository $userRepository,
        private MagazineRepository $magazineRepository,
        private CreateWrapper $createWrapper,
        private EntityManagerInterface $entityManager,
        private ActivityPubManager $activityPubManager,
        private SettingsManager $settingsManager
    ) {
    }

    public function __invoke(CreateMessage $message): void
    {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $entity = $this->entityManager->getRepository($message->type)->find($message->id);

        $activity = $this->createWrapper->build($entity);

        $followers = $this->userRepository->findAudience($entity->user);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }

        $followers = $this->activityPubManager->createCcFromObject($activity, $entity->user);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower, $activity));
        }

        $followers = $this->magazineRepository->findAudience($entity->magazine);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }
    }
}
