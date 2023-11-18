<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Pagination;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;
use Psr\Cache\CacheItemPoolInterface;

readonly class AdapterFactory
{
    public function __construct(
        private CacheItemPoolInterface $pool,
    ) {
    }

    public function create(QueryBuilder $queryBuilder): AdapterInterface
    {
        return new CachingQueryAdapter(
            new QueryAdapter(
                $queryBuilder,
                false,
                false,
            ),
            $this->pool,
        );
    }
}
