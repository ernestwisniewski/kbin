<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\UserNote;

use App\Entity\User;
use App\Entity\UserNote;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserNoteSave
{
    public function __construct(private UserNoteClear $userNoteClear, private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(User $user, User $target, string $body): UserNote
    {
        ($this->userNoteClear)($user, $target);

        $note = new UserNote($user, $target, $body);

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return $note;
    }
}
