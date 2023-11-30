<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Tag;

class TagJoinToBody
{
    public function __construct(private TagExtract $tagExtract)
    {
    }

    public function __invoke(string $body, array $tags): string
    {
        $current = ($this->tagExtract)($body, null) ?? [];

        $join = array_unique(array_merge(array_diff($tags, $current)));

        if (!empty($join)) {
            if (!empty($body)) {
                $lastTag = end($current);
                if (($lastTag && !str_ends_with($body, $lastTag)) || !$lastTag) {
                    $body = $body.PHP_EOL.PHP_EOL;
                }
            }

            $body = $body.' #'.implode(' #', $join);
        }

        return $body;
    }
}
