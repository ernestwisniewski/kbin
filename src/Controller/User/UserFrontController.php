<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
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
use App\Repository\SearchRepository;
use App\Repository\UserRepository;
use App\Service\SubjectOverviewManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserFrontController extends AbstractController
{
    public function __construct(private readonly SubjectOverviewManager $overviewManager)
    {
    }

    public function front(User $user, Request $request, UserRepository $repository): Response
    {
        $activity = $repository->findPublicActivity($this->getPageNb($request), $user);

        return $this->render(
            'user/overview.html.twig',
            [
                'user' => $user,
                'results' => $this->overviewManager->buildList($activity),
                'pagination' => $activity,
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
        $criteria->onlyParents = false;
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
            $children = $postOrComment->children;
            usort($children, fn ($a, $b) => $a->createdAt < $b->createdAt ? -1 : 1);
            foreach ($children as $child) {
                $results[] = $child;
            }
        }

        return $this->render(
            'user/replies.html.twig',
            [
                'user' => $user,
                'results' => $results,
                'pagination' => $comments,
            ]
        );
    }

    public function moderated(User $user, MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/moderated.html.twig',
            [
                'view' => 'list',
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

    public function following(User $user, UserRepository $manager, Request $request): Response
    {
        if (!$user->showProfileFollowings && !$user->apId) {
            if ($user !== $this->getUser()) {
                throw new AccessDeniedException();
            }
        }

        return $this->render(
            'user/following.html.twig',
            [
                'user' => $user,
                'users' => $manager->findFollowing($this->getPageNb($request), $user),
            ]
        );
    }

    public function boosts(User $user, Request $request, SearchRepository $repository)
    {
        $activity = $repository->findBoosts($this->getPageNb($request), $user);

        return $this->render(
            'user/overview.html.twig',
            [
                'user' => $user,
                'results' => $this->overviewManager->buildList($activity),
                'pagination' => $activity,
            ]
        );
    }
}
