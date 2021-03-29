<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\UserType;
use App\PageView\PostCommentPageView;
use App\PageView\PostPageView;
use App\Repository\EntryCommentRepository;
use App\Repository\MagazineRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\PageView\EntryCommentPageView;
use App\PageView\EntryPageView;
use App\Repository\EntryRepository;
use App\Repository\UserRepository;
use App\Entity\User;

class UserController extends AbstractController
{
    public function front(User $user, Request $request, UserRepository $userRepository): Response
    {
        return $this->render(
            'user/front.html.twig',
            [
                'user'    => $user,
                'results' => $userRepository->findPublicActivity((int) $request->get('strona', 1), $user),
            ]
        );
    }

    public function entries(User $user, Request $request, EntryRepository $entryRepository): Response
    {
        $criteria = (new EntryPageView((int) $request->get('strona', 1)))->showUser($user);

        return $this->render(
            'user/entries.html.twig',
            [
                'user'    => $user,
                'entries' => $entryRepository->findByCriteria($criteria),
            ]
        );
    }

    public function comments(User $user, Request $request, EntryCommentRepository $commentRepository): Response
    {
        $criteria = (new EntryCommentPageView((int) $request->get('strona', 1)))->showUser($user)->showOnlyParents(false);

        $comments = $commentRepository->findByCriteria($criteria);

        $commentRepository->hydrate(...$comments);
        $commentRepository->hydrateParents(...$comments);

        return $this->render(
            'user/comments.html.twig',
            [
                'user'     => $user,
                'comments' => $comments,
            ]
        );
    }

    public function posts(User $user, Request $request, PostRepository $postRepository): Response
    {
        $criteria = (new PostPageView((int) $request->get('strona', 1)))->showUser($user);

        $posts = $postRepository->findByCriteria($criteria);

        return $this->render(
            'user/posts.html.twig',
            [
                'user'  => $user,
                'posts' => $posts,
            ]
        );
    }

    public function replies(User $user, Request $request, PostCommentRepository $commentRepository): Response
    {
        $criteria = (new PostCommentPageView((int) $request->get('strona', 1)))->showUser($user);

        $comments = $commentRepository->findByCriteria($criteria);

        return $this->render(
            'user/replies.html.twig',
            [
                'user'     => $user,
                'comments' => $comments,
            ]
        );
    }

    public function subscriptions(User $user, MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'user/subscriptions.html.twig',
            [
                'user'      => $user,
                'magazines' => $magazineRepository->findSubscribedMagazines($page, $user),
            ]
        );
    }

    public function followers(User $user, UserRepository $userRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'user/followers.html.twig',
            [
                'user'  => $user,
                'users' => $userRepository->findFollowUsers($page, $user),
            ]
        );
    }

    public function follows(User $user, UserRepository $userRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'user/follows.html.twig',
            [
                'user'  => $user,
                'users' => $userRepository->findFollowedUsers($page, $user),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("follow", subject="following")
     */
    public function follow(User $following, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $userManager->follow($this->getUserOrThrow(), $following);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $following->getFollowersCount(),
                    'isSubscribed' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("follow", subject="following")
     */
    public function unfollow(User $following, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $userManager->unfollow($this->getUserOrThrow(), $following);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $following->getFollowersCount(),
                    'isSubscribed' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function block(User $blocked, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $userManager->block($this->getUserOrThrow(), $blocked);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function unblock(User $blocked, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $userManager->unblock($this->getUserOrThrow(), $blocked);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    public function edit(UserManager $userManager, Request $request)
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $userDto = $userManager->createDto($this->getUserOrThrow());

        $form = $this->createForm(UserType::class, $userDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->edit($this->getUser(), $userDto);

            if ($userDto->getPlainPassword()) {
                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('user_profile_edit');
        }

        return $this->render(
            'user/profile/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function theme(UserManager $userManager, Request $request): Response
    {
        $userManager->toggleTheme($this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'success' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
