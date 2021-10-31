<?php declare(strict_types = 1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\DTO\UserDto;
use App\Entity\User;
use App\Form\UserBasicType;
use App\Form\UserEmailType;
use App\Form\UserPasswordType;
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
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
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

    public function subscriptions(User $user, MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/subscriptions.html.twig',
            [
                'user'      => $user,
                'magazines' => $repository->findSubscribedMagazines($this->getPageNb($request), $user),
            ]
        );
    }

    public function edit(UserManager $manager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $dto = $manager->createDto($this->getUserOrThrow());

        $basicForm = $this->handleForm($this->createForm(UserBasicType::class, $dto), $dto, $manager, $request);
        if (!$basicForm instanceof FormInterface) {
            return $basicForm;
        }

        $emailForm = $this->handleForm($this->createForm(UserEmailType::class, $dto), $dto, $manager, $request);
        if (!$emailForm instanceof FormInterface) {
            return $emailForm;
        }

        $passwordForm = $this->handleForm($this->createForm(UserPasswordType::class, $dto), $dto, $manager, $request);
        if (!$passwordForm instanceof FormInterface) {
            return $passwordForm;
        }

        return $this->render(
            'user/profile/edit.html.twig',
            [
                'form_basic'    => $basicForm->createView(),
                'form_password' => $passwordForm->createView(),
                'form_email'    => $emailForm->createView(),
            ],
            new Response(
                null,
                $basicForm->isSubmitted() && !$basicForm->isValid()
                || $passwordForm->isSubmitted() && !$passwordForm->isValid()
                || $emailForm->isSubmitted() && !$emailForm->isValid()
                    ? 422 : 200
            )
        );
    }

    private function handleForm(FormInterface $form, UserDto $dto, UserManager $manager, Request $request): FormInterface|Response
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $this->getUser()->email;
            $manager->edit($this->getUser(), $dto);

            if ($dto->email !== $email || $dto->plainPassword) {
                $manager->logout();

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('user_profile_edit');
        }

        return $form;
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_delete', $request->request->get('token'));

        $manager->delete($user);

        return $this->redirectToRoute('front');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function purge(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_purge', $request->request->get('token'));

        $manager->delete($user, true);

        return $this->redirectToRoute('front');
    }

    public function theme(UserManager $manager, Request $request): Response
    {
        $manager->toggleTheme($this->getUserOrThrow());

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
