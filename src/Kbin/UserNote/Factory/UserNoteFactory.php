<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\UserNote\Factory;

use App\Entity\User;
use App\Kbin\UserNote\DTO\UserNoteDto;
use App\Repository\UserNoteRepository;

class UserNoteFactory
{
    public function __construct(private UserNoteRepository $userNoteRepository)
    {
    }

    public function createDto(User $user, User $target): UserNoteDto
    {
        $dto = new UserNoteDto();
        $dto->target = $target;

        $note = $this->userNoteRepository->findOneBy([
            'user' => $user,
            'target' => $target,
        ]);

        if ($note) {
            $dto->body = $note->body;
        }

        return $dto;
    }
}
