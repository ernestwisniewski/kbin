<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use App\Repository\PostCommentRepository;
use App\Repository\MagazineRepository;
use App\PageView\EntryCommentPageView;
use App\PageView\PostCommentPageView;
use App\Repository\EntryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\PageView\EntryPageView;
use App\PageView\PostPageView;
use App\Service\UserManager;
use App\Form\UserType;
use App\Entity\User;

class UserController extends AbstractController
{
    public function front(User $user, Request $request, UserRepository $userRepository): Response
    {
        return $this->render(
            'user/front.html.twig',
            [
                'user'    => $user,
                'results' => $userRepository->findPublicActivity($this->getPageNb($request), $user),
            ]
        );
    }

    public function entries(User $user, Request $request, EntryRepository $entryRepository): Response
    {
        $criteria       = new EntryPageView($this->getPageNb($request));
        $criteria->user = $user;

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
        $criteria              = new EntryCommentPageView($this->getPageNb($request));
        $criteria->user        = $user;
        $criteria->onlyParents = false;

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
        $criteria       = new PostPageView($this->getPageNb($request));
        $criteria->user = $user;

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
        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->user = $user;

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
        return $this->render(
            'user/subscriptions.html.twig',
            [
                'user'      => $user,
                'magazines' => $magazineRepository->findSubscribedMagazines($this->getPageNb($request), $user),
            ]
        );
    }

    public function edit(UserManager $userManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $userDto = $userManager->createDto($this->getUserOrThrow());

        $form = $this->createForm(UserType::class, $userDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->edit($this->getUser(), $userDto);

            if ($userDto->plainPassword) {
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
