<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\PageView\EntryCommentPageView;
use App\PageView\EntryPageView;
use App\PageView\PostCommentPageView;
use App\PageView\PostPageView;
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
        return $this->render(
            'user/front.html.twig',
            [
                'user'    => $user,
                'results' => $repository->findPublicActivity($this->getPageNb($request), $user),
            ]
        );
    }

    public function entries(User $user, Request $request, EntryRepository $repository): Response
    {
        $criteria       = new EntryPageView($this->getPageNb($request));
        $criteria->user = $user;

        return $this->render(
            'user/entries.html.twig',
            [
                'user'    => $user,
                'entries' => $repository->findByCriteria($criteria),
            ]
        );
    }

    public function comments(User $user, Request $request, EntryCommentRepository $repository): Response
    {
        $criteria              = new EntryCommentPageView($this->getPageNb($request));
        $criteria->user        = $user;
        $criteria->onlyParents = false;

        $comments = $repository->findByCriteria($criteria);

        $repository->hydrate(...$comments);
        $repository->hydrateParents(...$comments);

        return $this->render(
            'user/comments.html.twig',
            [
                'user'     => $user,
                'comments' => $comments,
            ]
        );
    }

    public function posts(User $user, Request $request, PostRepository $repository): Response
    {
        $criteria       = new PostPageView($this->getPageNb($request));
        $criteria->user = $user;

        $posts = $repository->findByCriteria($criteria);

        return $this->render(
            'user/posts.html.twig',
            [
                'user'  => $user,
                'posts' => $posts,
            ]
        );
    }

    public function replies(User $user, Request $request, PostCommentRepository $repository): Response
    {
        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->user = $user;

        $comments = $repository->findByCriteria($criteria);

        return $this->render(
            'user/replies.html.twig',
            [
                'user'     => $user,
                'comments' => $comments,
            ]
        );
    }

    public function moderated(User $user, MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/moderated.html.twig',
            [
                'user'      => $user,
                'magazines' => $repository->findModeratedMagazines($user, (int) $request->get('p', 1)),
            ]
        );
    }

    public function subscriptions(User $user, MagazineRepository $repository, Request $request): Response
    {
        if (!$user->showProfileSubscriptions) {
            throw new AccessDeniedException();
        }

        return $this->render(
            'user/subscriptions.html.twig',
            [
                'user'      => $user,
                'magazines' => $repository->findSubscribedMagazines($this->getPageNb($request), $user),
            ]
        );
    }

    public function followers(User $user, UserRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/followers.html.twig',
            [
                'user'  => $user,
                'users' => $repository->findFollowUsers($this->getPageNb($request), $user),
            ]
        );
    }

    public function follows(User $user, UserRepository $manager, Request $request): Response
    {
        if (!$user->showProfileFollowings) {
            throw new AccessDeniedException();
        }

        return $this->render(
            'user/follows.html.twig',
            [
                'user'  => $user,
                'users' => $manager->findFollowedUsers($this->getPageNb($request), $user),
            ]
        );
    }
}
