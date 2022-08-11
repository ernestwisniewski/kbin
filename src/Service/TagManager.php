<?php declare(strict_types=1);

namespace App\Service;

use App\Utils\RegPatterns;

class TagManager
{
    public function extract(string $val, ?string $magazineName = null): ?array
    {
        preg_match_all(RegPatterns::LOCAL_TAG, $val, $matches);

        $result = $matches[1];
        $result = array_map(fn($tag) => strtolower(trim($tag)), $result);
        if ($magazineName) {
            $result = array_diff($result, [$magazineName]);
        }

        return count($result) ? array_unique(array_values($result)) : null;
    }
}
