<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class UrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack
    ) {
    }

    public function entryUrl(Entry $entry): string
    {
        return $this->urlGenerator->generate('entry_single', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => '' === $entry->slug ? 'icon' : $entry->slug,
        ]);
    }

    public function entryFavouritesUrl(Entry $entry): string
    {
        return $this->urlGenerator->generate('entry_favourites', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => '' === $entry->slug ? 'icon' : $entry->slug,
        ]);
    }

    public function entryVotersUrl(Entry $entry, string $type): string
    {
        return $this->urlGenerator->generate('entry_voters', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => '' === $entry->slug ? 'icon' : $entry->slug,
            'type' => $type,
        ]);
    }

    public function entryCommentCreateUrl(EntryComment $comment): string
    {
        return $this->urlGenerator->generate('entry_comment_create', [
            'magazine_name' => $comment->magazine->name,
            'entry_id' => $comment->entry->getId(),
            'slug' => empty($comment->entry->slug) ? 'icon' : $comment->entry->slug,
            'parent_comment_id' => $comment->getId(),
        ]);
    }

    public function postUrl(Post $post): string
    {
        return $this->urlGenerator->generate('post_single', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => '' === $post->slug ? 'icon' : $post->slug,
        ]);
    }

    public function postFavouritesUrl(Post $post): string
    {
        return $this->urlGenerator->generate('post_favourites', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => '' === $post->slug ? 'icon' : $post->slug,
        ]);
    }

    public function postVotersUrl(Post $post, string $type): string
    {
        return $this->urlGenerator->generate('post_voters', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => '' === $post->slug ? 'icon' : $post->slug,
            'type' => $type,
        ]);
    }

    public function postCommentReplyUrl(PostComment $comment): string
    {
        return $this->urlGenerator->generate('post_comment_create', [
            'magazine_name' => $comment->magazine->name,
            'post_id' => $comment->post->getId(),
            'slug' => empty($comment->post->slug) ? 'icon' : $comment->post->slug,
            'parent_comment_id' => $comment->getId(),
        ]);
    }

    public function optionsUrl(string $name, string $value): string
    {
        $route = $this->requestStack->getCurrentRequest()->attributes->get('_route');
        $params = $this->requestStack->getCurrentRequest()->attributes->all()['_route_params'];

        $params[$name] = $value;

        return $this->urlGenerator->generate($route, $params);
    }
}
