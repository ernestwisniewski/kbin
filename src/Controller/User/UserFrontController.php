<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\PageView\EntryCommentPageView;
use App\PageView\EntryPageView;
use App\PageView\PostCommentPageView;
use App\PageView\PostPageView;
use App\Repository\Criteria;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserFrontController extends AbstractController
{
    public function front(User $user, Request $request, UserRepository $repository): Response
    {
        $activity = $pagination = $repository->findPublicActivity($this->getPageNb($request), $user);

        $postsAndEntries = array_filter(
            $activity->getCurrentPageResults(),
            fn ($val) => $val instanceof Entry || $val instanceof Post
        );
        $comments = array_filter(
            $activity->getCurrentPageResults(),
            fn ($val) => $val instanceof EntryComment || $val instanceof PostComment
        );

        $results = [];
        foreach ($postsAndEntries as $parent) {
            if ($parent instanceof Entry) {
                $children = array_filter(
                    $comments,
                    fn ($val) => $val instanceof EntryComment && $val->entry === $parent
                );
                $comments = array_filter(
                    $comments,
                    fn ($val) => $val instanceof PostComment || $val instanceof EntryComment && $val->entry !== $parent
                );
            } else {
                $children = array_filter(
                    $comments,
                    fn ($val) => $val instanceof PostComment && $val->post === $parent
                );
                $comments = array_filter(
                    $comments,
                    fn ($val) => $val instanceof EntryComment || $val instanceof PostComment && $val->post !== $parent
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

        uasort($merged, fn ($a, $b) => $a->getCreatedAt() > $b->getCreatedAt() ? -1 : 1);

        $results = [];
        foreach ($merged as $entry) {
            $results[] = $entry;
            uasort($entry->children, fn ($a, $b) => $a->getCreatedAt() < $b->getCreatedAt() ? -1 : 1);
            foreach ($entry->children as $child) {
                $results[] = $child;
            }
        }

        return $this->render(
            'user/front.html.twig',
            [
                'user' => $user,
                'results' => $results,
                'pagination' => $pagination,
            ]
        );
    }

    public function entries(User $user, Request $request, EntryRepository $repository): Response
    {
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->sortOption = Criteria::SORT_NEW;
        $criteria->user = $user;

        return $this->render(
            'user/entries.html.twig',
            [
                'user' => $user,
                'entries' => $repository->findByCriteria($criteria),
            ]
        );
    }

    public function comments(User $user, Request $request, EntryCommentRepository $repository): Response
    {
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->sortOption = Criteria::SORT_NEW;
        $criteria->user = $user;
        $criteria->onlyParents = false;

        $comments = $repository->findByCriteria($criteria);

        $repository->hydrate(...$comments);
        $repository->hydrateParents(...$comments);

        return $this->render(
            'user/comments.html.twig',
            [
                'user' => $user,
                'comments' => $comments,
            ]
        );
    }

    public function posts(User $user, Request $request, PostRepository $repository): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->sortOption = Criteria::SORT_NEW;
        $criteria->user = $user;

        $posts = $repository->findByCriteria($criteria);

        return $this->render(
            'user/posts.html.twig',
            [
                'user' => $user,
                'posts' => $posts,
            ]
        );
    }

    public function replies(User $user, Request $request, PostCommentRepository $repository): Response
    {
        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->sortOption = Criteria::SORT_NEW;
        $criteria->user = $user;

        $comments = $repository->findByCriteria($criteria);

        $parents = [];
        foreach ($comments as $comment) {
            $inParents = false;
            $parent = $comment->post;

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

        $results = [];
        foreach ($parents as $postOrComment) {
            $results[] = $postOrComment;
            foreach ($postOrComment->children as $child) {
                $results[] = $child;
            }
        }

        return $this->render(
            'user/replies.html.twig',
            [
                'user' => $user,
                'results' => $results,
                'comments' => $comments,
            ]
        );
    }

    public function moderated(User $user, MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/moderated.html.twig',
            [
                'user' => $user,
                'magazines' => $repository->findModeratedMagazines($user, (int) $request->get('p', 1)),
            ]
        );
    }

    public function subscriptions(User $user, MagazineRepository $repository, Request $request): Response
    {
        if (!$user->showProfileSubscriptions) {
            if ($user !== $this->getUser()) {
                throw new AccessDeniedException();
            }
        }

        return $this->render(
            'user/subscriptions.html.twig',
            [
                'user' => $user,
                'magazines' => $repository->findSubscribedMagazines($this->getPageNb($request), $user),
            ]
        );
    }

    public function followers(User $user, UserRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/followers.html.twig',
            [
                'user' => $user,
                'users' => $repository->findFollowers($this->getPageNb($request), $user),
            ]
        );
    }

    public function follows(User $user, UserRepository $manager, Request $request): Response
    {
        if (!$user->showProfileFollowings && !$user->apId) {
            if ($user !== $this->getUser()) {
                throw new AccessDeniedException();
            }
        }

        return $this->render(
            'user/follows.html.twig',
            [
                'user' => $user,
                'users' => $manager->findFollowing($this->getPageNb($request), $user),
            ]
        );
    }
}
