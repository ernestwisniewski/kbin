<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\People;

use App\Entity\Magazine;
use App\Repository\EntryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;

readonly class PeopleByMagazine
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private EntryRepository $entryRepository
    ) {
    }

    public function __invoke(Magazine $magazine, bool $federated = false): array
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

        $users = array_merge(
            $this->postRepository->findUsers($magazine),
            $this->entryRepository->findUsers($magazine)
        );

        return $this->sort(
            $this->userRepository->findBy(['id' => array_map(fn ($val) => $val['id'], $users)]),
            $users
        );
    }

    private function sort(array $users, array $ids): array
    {
        usort($ids, fn ($a, $b) => $a['count'] < $b['count']);
        $ids = array_reduce($ids, function ($carry, $item) {
            $id = $item['id'];
            if (!isset($carry[$id])) {
                $carry[$id] = $item;
            }

            return $carry;
        }, []);

        $result = [];
        foreach ($ids as $id) {
            $result[] = array_values(array_filter($users, fn ($val) => $val->getId() === $id['id']))[0];
        }

        return array_values($result);
    }
}
