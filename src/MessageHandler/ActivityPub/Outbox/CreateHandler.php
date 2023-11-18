<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Kbin\User\UserReputationGet;
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
        private readonly SettingsManager $settingsManager,
        private readonly UserReputationGet $userReputationGet
    ) {
    }

    public function __invoke(CreateMessage $message): void
    {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $entity = $this->entityManager->getRepository($message->type)->find($message->id);

        if ($this->settingsManager->get('KBIN_SPAM_PROTECTION')) {
            if (($this->userReputationGet)($entity->user) < 8 && $entity->user->spamProtection) {
                return;
            }
        }

        $activity = $this->createWrapper->build($entity);

        $this->deliver(array_filter($this->userRepository->findAudience($entity->user)), $activity);
        $this->deliver(
            array_filter($this->activityPubManager->createInboxesFromCC($activity, $entity->user)),
            $activity
        );
        $this->deliver(array_filter($this->magazineRepository->findAudience($entity->magazine)), $activity);
    }

    private function deliver(array $followers, array $activity): void
    {
        foreach ($followers as $follower) {
            if (!$follower) {
                continue;
            }

            $inboxUrl = \is_string($follower) ? $follower : $follower->apInboxUrl;

            if ($this->settingsManager->isBannedInstance($inboxUrl)) {
                continue;
            }

            $this->bus->dispatch(new DeliverMessage($follower, $activity));
        }
    }
}
