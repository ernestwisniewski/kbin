<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Tag;

class TagTransliterate
{
    public function __invoke(string $tag): string
    {
        $transliterator = \Transliterator::create('Latin-ASCII');
        $removerRule = \Transliterator::createFromRules(':: [:Nonspacing Mark:] Remove;');

        return iconv('UTF-8', 'ASCII//TRANSLIT', $removerRule->transliterate($transliterator->transliterate($tag)));
    }
}
