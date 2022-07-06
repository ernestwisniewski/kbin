<?php declare(strict_types=1);

namespace App\Service;

class TagManager
{
    public function extract(string $val, string $magazineName): ?array
    {
        preg_match_all("/\B#(\w{2,35})/", $val, $matches);

        $result = $matches[1];
        $result = array_map(fn($tag) => strtolower(trim($tag)), $result);
        $result = array_diff($result, [$magazineName]);

        return count($result) ? array_unique(array_values($result)) : null;
    }
}
