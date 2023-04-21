<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\CreateWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly CreateWrapper $createWrapper,
        private readonly EntityManagerInterface $entityManager,
        private readonly ActivityPubManager $activityPubManager,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function __invoke(CreateMessage $message): void
    {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $entity = $this->entityManager->getRepository($message->type)->find($message->id);

        $activity = $this->createWrapper->build($entity);

        $this->deliver($this->userRepository->findAudience($entity->user), $activity);
        $this->deliver($this->activityPubManager->createCcFromObject($activity, $entity->user), $activity);
        $this->deliver($this->magazineRepository->findAudience($entity->magazine), $activity);
    }

    private function deliver(array $followers, array $activity)
    {
        foreach ($followers as $follower) {
            if (is_string($follower)) {
                $this->bus->dispatch(new DeliverMessage($follower, $activity));

                return;
            }

            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }
    }
}
