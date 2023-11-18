<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TagsWrapper
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function build(array $tags): array
    {
        return array_map(fn ($tag) => [
            'type' => 'Hashtag',
            'href' => $this->urlGenerator->generate(
                'tag_overview',
                ['name' => $tag],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'tag' => '#'.$tag,
        ], $tags);
    }
}
