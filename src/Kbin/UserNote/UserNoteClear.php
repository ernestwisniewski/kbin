<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\UserNote;

use App\Entity\User;
use App\Repository\UserNoteRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserNoteClear
{
    public function __construct(
        private UserNoteRepository $userNoteRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, User $target): void
    {
        $note = $this->userNoteRepository->findOneBy([
            'user' => $user,
            'target' => $target,
        ]);

        if ($note) {
            $this->entityManager->remove($note);
            $this->entityManager->flush();
        }
    }
}
