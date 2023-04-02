<?php

namespace App\Twig\Components;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Twig\Runtime\UserExtensionRuntime;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('related_posts')]
final class RelatedPostsComponent
{
    public const TYPE_TAG = 'tag';
    public const TYPE_MAGAZINE = 'magazine';
    public const TYPE_RANDOM = 'random';

    public int $limit = 4;
    public ?string $tag = null;
    public ?string $magazine = null;
    public ?string $type = self::TYPE_RANDOM;
    public ?Post $post = null;
    public string $title = 'random_posts';

    public function __construct(private readonly PostRepository $repository)
    {
    }

    #[PostMount]
    public function postMount(array $attr): array
    {
        if ($this->tag) {
            $this->title = 'related_posts';
            $this->type = self::TYPE_TAG;
        }

        if ($this->magazine) {
            $this->title = 'related_posts';
            $this->type = self::TYPE_MAGAZINE;
        }

        return $attr;
    }

    public function getPosts(): iterable
    {
        $posts = match ($this->type) {
            self::TYPE_TAG => $this->repository->findRelatedByMagazine($this->tag, $this->limit + 20),
            self::TYPE_MAGAZINE => $this->repository->findRelatedByTag(UserExtensionRuntime::username($this->magazine), $this->limit + 20),
            default => $this->repository->findLast($this->limit + 50),
        };

        if (count($posts) > $this->limit) {
            shuffle($posts); // randomize the order
            $posts = array_slice($posts, 0, $this->limit);
        }

        return $posts;
    }
}
