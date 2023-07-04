<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly MessageBusInterface $bus,
        private readonly ActivityPubManager $activityPubManager,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function __invoke(DeleteMessage $message): void
    {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $user = $this->userRepository->find($message->userId);
        $magazine = $this->magazineRepository->find($message->magazineId);

        $this->deliver(array_filter($this->userRepository->findAudience($user)), $message->payload);
        $this->deliver(
            array_filter($this->activityPubManager->createInboxesFromCC($message->payload, $user)),
            $message->payload
        );
        $this->deliver(array_filter($this->magazineRepository->findAudience($magazine)), $message->payload);
    }

    private function deliver(array $followers, array $activity)
    {
        foreach ($followers as $follower) {
            if (!$follower) {
                continue;
            }
            if (is_string($follower)) {
                $this->bus->dispatch(new DeliverMessage($follower, $activity));
                continue;
            }
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }
    }
}
