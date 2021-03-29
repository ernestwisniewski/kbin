<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\UserType;
use App\PageView\PostPageView;
use App\Repository\EntryCommentRepository;
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
