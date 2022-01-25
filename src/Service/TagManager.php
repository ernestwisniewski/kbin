<?php declare(strict_types=1);

namespace App\Service;

class TagManager
{
    public function extract(string $val): ?array
    {
        preg_match_all("/#(\w+)/", $val, $matches);

        $result = array_unique($matches[1]);

        return count($result) ? $result : null;
    }
}
