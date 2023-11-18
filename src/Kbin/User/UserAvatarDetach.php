<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Message\DeleteImageMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class UserAvatarDetach
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user): void
    {
        if (!$user->avatar) {
            return;
        }

        $image = $user->avatar->filePath;

        $user->avatar = null;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new DeleteImageMessage($image));
    }
}
