<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Factory\FavouriteFactory;
use App\Repository\FavouriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class FavouriteManager
{
    public const TYPE_LIKE = 'like';
    public const TYPE_UNLIKE = 'unlike';

    public function __construct(
        private readonly FavouriteFactory $factory,
        private readonly FavouriteRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }
}
