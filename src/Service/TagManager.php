<?php declare(strict_types=1);

namespace App\Service;

use App\Utils\RegPatterns;

class TagManager
{
    public function extract(string $val, ?string $magazineName = null, $withCanonical = true): ?array
    {
        preg_match_all(RegPatterns::LOCAL_TAG, $val, $matches);

        $result = $matches[1];
        $result = array_map(fn($tag) => strtolower(trim($tag)), $result);
        if ($magazineName) {
            $result = array_diff($result, [$magazineName]);
        }

        if ($withCanonical) {
            $result = array_values($result);

            $canonical = array_map(fn($tag) => iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $tag), $result);

            $result = array_merge($result, $canonical);
        }

        return count($result) ? array_unique(array_values($result)) : null;
    }

    public function joinTagsToBody(string $body, array $tags): string
    {
        $current = $this->extract($body, null, false) ?? [];

        $join = array_unique(array_merge(array_diff($tags, $current)));

        if (!empty($join)) {
            if (!empty($body)) {
                $body = $body.'<br><br>';
            }

            $body = '#'.implode(' #', $join).' '.$body;
        }

        return $body;
    }
}
