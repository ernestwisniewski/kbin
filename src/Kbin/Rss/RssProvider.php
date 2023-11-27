<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Rss;

use Debril\RssAtomBundle\Provider\FeedProviderInterface;
use FeedIo\FeedInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class RssProvider implements FeedProviderInterface
{
    public function __construct(private RssFeedCreate $rssFeedCreate)
    {
    }

    public function getFeed(Request $request): FeedInterface
    {
        return ($this->rssFeedCreate)($request);
    }
}
