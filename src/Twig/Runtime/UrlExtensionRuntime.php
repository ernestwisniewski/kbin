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
            'slug' => empty($entry->slug) ? '-' : $entry->slug,
        ]);
    }

    public function entryFavouritesUrl(Entry $entry): string
    {
        return $this->urlGenerator->generate('entry_fav', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => empty($entry->slug) ? '-' : $entry->slug,
        ]);
    }

    public function entryVotersUrl(Entry $entry, string $type): string
    {
        return $this->urlGenerator->generate('entry_voters', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => empty($entry->slug) ? '-' : $entry->slug,
            'type' => $type,
        ]);
    }

    public function entryEditUrl(Entry $entry): string
    {
        return $this->urlGenerator->generate('entry_edit', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => empty($entry->slug) ? '-' : $entry->slug,
        ]);
    }

    public function entryModerateUrl(Entry $entry): string
    {
        return $this->urlGenerator->generate('entry_moderate', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => empty($entry->slug) ? '-' : $entry->slug,
        ]);
    }

    public function entryDeleteUrl(Entry $entry): string
    {
        return $this->urlGenerator->generate('entry_delete', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => empty($entry->slug) ? '-' : $entry->slug,
        ]);
    }

    public function entryCommentCreateUrl(EntryComment $comment): string
    {
        return $this->urlGenerator->generate('entry_comment_create', [
            'magazine_name' => $comment->magazine->name,
            'entry_id' => $comment->entry->getId(),
            'slug' => empty($comment->entry->slug) ? '-' : $comment->entry->slug,
            'parent_comment_id' => $comment->getId(),
        ]);
    }

    public function entryCommentEditUrl(EntryComment $comment): string
    {
        return $this->urlGenerator->generate('entry_comment_edit', [
            'magazine_name' => $comment->magazine->name,
            'entry_id' => $comment->entry->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->entry->slug) ? '-' : $comment->entry->slug,
        ]);
    }

    public function entryCommentDeleteUrl(EntryComment $comment): string
    {
        return $this->urlGenerator->generate('entry_comment_delete', [
            'magazine_name' => $comment->magazine->name,
            'entry_id' => $comment->entry->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->entry->slug) ? '-' : $comment->entry->slug,
        ]);
    }

    public function entryCommentVotersUrl(EntryComment $comment, string $type): string
    {
        return $this->urlGenerator->generate('entry_comment_voters', [
            'magazine_name' => $comment->magazine->name,
            'entry_id' => $comment->entry->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->entry->slug) ? '-' : $comment->entry->slug,
            'type' => $type,
        ]);
    }

    public function entryCommentFavouritesUrl(EntryComment $comment): string
    {
        return $this->urlGenerator->generate('entry_comment_favourites', [
            'magazine_name' => $comment->magazine->name,
            'entry_id' => $comment->entry->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->entry->slug) ? '-' : $comment->entry->slug,
        ]);
    }

    public function entryCommentModerateUrl(EntryComment $comment): string
    {
        return $this->urlGenerator->generate('entry_comment_moderate', [
            'magazine_name' => $comment->magazine->name,
            'entry_id' => $comment->entry->getId(),
            'slug' => empty($comment->entry->slug) ? '-' : $comment->entry->slug,
        ]);
    }

    public function postUrl(Post $post): string
    {
        return $this->urlGenerator->generate('post_single', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => empty($post->slug) ? '-' : $post->slug,
        ]);
    }

    public function postEditUrl(Post $post): string
    {
        return $this->urlGenerator->generate('post_edit', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => empty($post->slug) ? '-' : $post->slug,
        ]);
    }

    public function postFavouritesUrl(Post $post): string
    {
        return $this->urlGenerator->generate('post_favourites', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => empty($post->slug) ? '-' : $post->slug,
        ]);
    }

    public function postVotersUrl(Post $post, string $type): string
    {
        return $this->urlGenerator->generate('post_voters', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => empty($post->slug) ? '-' : $post->slug,
            'type' => $type,
        ]);
    }

    public function postModerateUrl(Post $post): string
    {
        return $this->urlGenerator->generate('post_moderate', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => empty($post->slug) ? '-' : $post->slug,
        ]);
    }

    public function postDeleteUrl(Post $post): string
    {
        return $this->urlGenerator->generate('post_delete', [
            'magazine_name' => $post->magazine->name,
            'post_id' => $post->getId(),
            'slug' => empty($post->slug) ? '-' : $post->slug,
        ]);
    }

    public function postCommentReplyUrl(PostComment $comment): string
    {
        return $this->urlGenerator->generate('post_comment_create', [
            'magazine_name' => $comment->magazine->name,
            'post_id' => $comment->post->getId(),
            'slug' => empty($comment->post->slug) ? '-' : $comment->post->slug,
            'parent_comment_id' => $comment->getId(),
        ]);
    }

    public function postCommentEditUrl(PostComment $comment): string
    {
        return $this->urlGenerator->generate('post_comment_edit', [
            'magazine_name' => $comment->magazine->name,
            'post_id' => $comment->post->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->post->slug) ? '-' : $comment->post->slug,
        ]);
    }

    public function postCommentModerateUrl(PostComment $comment): string
    {
        return $this->urlGenerator->generate('post_comment_moderate', [
            'magazine_name' => $comment->magazine->name,
            'post_id' => $comment->post->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->post->slug) ? '-' : $comment->post->slug,
        ]);
    }

    public function postCommentVotersUrl(PostComment $comment): string
    {
        return $this->urlGenerator->generate('post_comment_voters', [
            'magazine_name' => $comment->magazine->name,
            'post_id' => $comment->post->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->post->slug) ? '-' : $comment->post->slug,
        ]);
    }

    public function postCommentFavouritesUrl(PostComment $comment): string
    {
        return $this->urlGenerator->generate('post_comment_favourites', [
            'magazine_name' => $comment->magazine->name,
            'post_id' => $comment->post->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->post->slug) ? '-' : $comment->post->slug,
        ]);
    }

    public function postCommentDeleteUrl(PostComment $comment): string
    {
        return $this->urlGenerator->generate('post_comment_delete', [
            'magazine_name' => $comment->magazine->name,
            'post_id' => $comment->post->getId(),
            'comment_id' => $comment->getId(),
            'slug' => empty($comment->post->slug) ? '-' : $comment->post->slug,
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
