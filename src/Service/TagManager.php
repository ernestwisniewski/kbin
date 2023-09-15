<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\RegPatterns;

class TagManager
{
    public function joinTagsToBody(string $body, array $tags): string
    {
        $current = $this->extract($body, null, false) ?? [];

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

    public function extract(string $val, string $magazineName = null): ?array
    {
        preg_match_all(RegPatterns::LOCAL_TAG, $val, $matches);

        $result = $matches[1];
        $result = array_map(fn ($tag) => strtolower(trim($tag)), $result);

        $result = array_values($result);

        $result = array_map(fn ($tag) => $this->transliterate($tag), $result);

        if ($magazineName) {
            $result = array_diff($result, [$magazineName]);
        }

        return \count($result) ? array_unique(array_values($result)) : null;
    }

    public function transliterate(string $tag): string
    {
        $transliterator = \Transliterator::create('Latin-ASCII');
        $removerRule = \Transliterator::createFromRules(':: [:Nonspacing Mark:] Remove;');

        return iconv('UTF-8', 'ASCII//TRANSLIT', $removerRule->transliterate($transliterator->transliterate($tag)));
    }
}
