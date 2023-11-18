<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Entity\Magazine;
use App\Repository\PostRepository;
use App\Repository\UserRepository;

class PeopleManager
{
    public ?Magazine $magazine = null;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository,
    ) {
    }

    public function byMagazine(Magazine $magazine, bool $federated = false): array
    {
        if ($federated) {
            $users = $this->postRepository->findUsers($magazine, true);

            return $this->sort(
                $this->userRepository->findBy(
                    ['id' => array_map(fn ($val) => $val['id'], $users)]
                ),
                $users
            );
        }

        $local = $this->postRepository->findUsers($magazine);

        return $this->sort(
            $this->userRepository->findBy(['id' => array_map(fn ($val) => $val['id'], $local)]),
            $local
        );
    }

    private function sort(array $users, array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = array_values(array_filter($users, fn ($val) => $val->getId() === $id['id']))[0];
        }

        return array_values($result);
    }

    public function general(bool $federated = false): array
    {
        if ($federated) {
            return $this->userRepository->findWithAbout(UserRepository::USERS_REMOTE);
        }

        return $this->userRepository->findWithAbout(UserRepository::USERS_LOCAL);
    }
}
