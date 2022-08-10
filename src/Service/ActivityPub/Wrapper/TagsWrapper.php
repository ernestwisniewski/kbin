<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

class TagsWrapper
{
    public function __construct()
    {
    }


    public function build(?array $tags): array
    {
        $tags = $entry->tags ?? [];

        return array_map(fn($tag) => [
            'type' => 'Hashtag',
            'href' => '', // @todo tags endpoints
            'name' => '#'.$tag,
        ], $tags);
    }
}
