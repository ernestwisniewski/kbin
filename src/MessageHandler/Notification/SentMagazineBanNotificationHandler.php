<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\MagazineBanNotificationMessage;
use App\Repository\MagazineBanRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class SentMagazineBanNotificationHandler
{
    public function __construct(
        private readonly MagazineBanRepository $repository,
        private readonly NotificationManager $manager
    ) {
    }

    public function __invoke(MagazineBanNotificationMessage $message)
    {
        $ban = $this->repository->find($message->banId);

        if (!$ban) {
            throw new UnrecoverableMessageHandlingException('Ban not found');
        }

        $this->manager->sendMagazineBanNotification($ban);
    }
}
