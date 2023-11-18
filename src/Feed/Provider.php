<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Feed;

use App\Service\FeedManager;
use Debril\RssAtomBundle\Provider\FeedProviderInterface;
use FeedIo\FeedInterface;
use Symfony\Component\HttpFoundation\Request;

class Provider implements FeedProviderInterface
{
    public function __construct(private readonly FeedManager $manager)
    {
    }

    public function getFeed(Request $request): FeedInterface
    {
        return $this->manager->getFeed($request);
    }

    protected function getItems(): \Generator
    {
        yield $this->manager->getItems();
    }
}
