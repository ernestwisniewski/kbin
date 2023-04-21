<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Pagerfanta\PagerfantaInterface;

class SubjectOverviewManager
{
    public function buildList(PagerfantaInterface $activity): array
    {
        $postsAndEntries = array_filter(
            $activity->getCurrentPageResults(),
            fn($val) => $val instanceof Entry || $val instanceof Post
        );
        $comments = array_filter(
            $activity->getCurrentPageResults(),
            fn($val) => $val instanceof EntryComment || $val instanceof PostComment
        );

        $results = [];
        foreach ($postsAndEntries as $parent) {
            if ($parent instanceof Entry) {
                $children = array_filter(
                    $comments,
                    fn($val) => $val instanceof EntryComment && $val->entry === $parent
                );
                $comments = array_filter(
                    $comments,
                    fn($val) => $val instanceof PostComment || $val instanceof EntryComment && $val->entry !== $parent
                );
            } else {
                $children = array_filter(
                    $comments,
                    fn($val) => $val instanceof PostComment && $val->post === $parent
                );
                $comments = array_filter(
                    $comments,
                    fn($val) => $val instanceof EntryComment || $val instanceof PostComment && $val->post !== $parent
                );
            }

            $results[] = $parent;

            foreach ($children as $child) {
                $parent->children[] = $child;
            }
        }

        $parents = [];
        foreach ($comments as $comment) {
            $inParents = false;
            $parent = $comment->entry ?? $comment->post;

            foreach ($parents as $val) {
                if ($val instanceof $parent && $parent === $val) {
                    $val->children[] = $comment;
                    $inParents = true;
                }
            }

            if (!$inParents) {
                $parent->children[] = $comment;
                $parents[] = $parent;
            }
        }

        $merged = array_merge($results, $parents);

        uasort($merged, fn($a, $b) => $a->getCreatedAt() > $b->getCreatedAt() ? -1 : 1);

        $results = [];
        foreach ($merged as $entry) {
            $results[] = $entry;
            uasort($entry->children, fn($a, $b) => $a->getCreatedAt() < $b->getCreatedAt() ? -1 : 1);
            foreach ($entry->children as $child) {
                $results[] = $child;
            }
        }

        return $results;
    }
}
