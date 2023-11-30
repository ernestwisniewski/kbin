<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Tag;

use App\Utils\RegPatterns;

class TagExtract
{
    public function __construct(private TagTransliterate $tagTransliterate)
    {
    }

    public function __invoke(string $value, string $magazineName = null): ?array
    {
        preg_match_all(RegPatterns::LOCAL_TAG, $value, $matches);

        $result = $matches[1];
        $result = array_map(fn ($tag) => strtolower(trim($tag)), $result);

        $result = array_values($result);

        $result = array_map(fn ($tag) => ($this->tagTransliterate)($tag), $result);

        if ($magazineName) {
            $result = array_diff($result, [$magazineName]);
        }

        return \count($result) ? array_unique(array_values($result)) : null;
    }
}
