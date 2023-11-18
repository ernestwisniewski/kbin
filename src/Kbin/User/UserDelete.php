<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Message\DeleteUserMessage;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class UserDelete
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(User $user, bool $purge = false, bool $contentOnly = false): void
    {
        $this->messageBus->dispatch(new DeleteUserMessage($user->getId(), $purge, $contentOnly));
    }
}
