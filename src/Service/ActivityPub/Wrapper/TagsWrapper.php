<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use ApiPlatform\Core\Api\UrlGeneratorInterface;

class TagsWrapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
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
