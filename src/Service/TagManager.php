<?php declare(strict_types=1);

namespace App\Service;

class TagManager
{
    public function extract(string $val, string $magazineName): ?array
    {
        preg_match_all("/#(\w+)/", $val, $matches);

        $result = array_unique($matches[1]);
        $result = array_map(fn($tag) => strtolower(trim($tag)), $result);
        $result = array_diff($result, [$magazineName]);

        return  count($result) ? array_values($result) : null;
    }
}
