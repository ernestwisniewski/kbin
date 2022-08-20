<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TagsWrapper
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }


    public function build(array $tags): array
    {
        return array_map(fn($tag) => [
            'type' => 'Hashtag',
            'href' => $this->urlGenerator->generate('tag_overall', ['name' => $tag], UrlGeneratorInterface::ABSOLUTE_URL),
            'name' => '#'.$tag,
        ], $tags);
    }
}
