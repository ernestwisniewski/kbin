<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\DTO\UserDto;
use App\Form\UserBasicType;
use App\Form\UserEmailType;
use App\Form\UserPasswordType;
use App\Service\UserManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserEditController extends AbstractController
{
    public function __construct(
        private readonly UserManager $manager,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function general(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $dto = $this->manager->createDto($this->getUserOrThrow());

        $form = $this->handleForm($this->createForm(UserBasicType::class, $dto), $dto, $request);
        if (!$form instanceof FormInterface) {
            return $form;
        }

        return $this->render(
            'user/settings/profile.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(
                null,
                $form->isSubmitted() && !$form->isValid() ? 422 : 200
            )
        );
    }

    #[IsGranted('ROLE_USER')]
    public function email(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $dto = $this->manager->createDto($this->getUserOrThrow());

        $form = $this->handleForm($this->createForm(UserEmailType::class, $dto), $dto, $request);
        if (!$form instanceof FormInterface) {
            return $form;
        }

        return $this->render(
            'user/settings/email.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(
                null,
                $form->isSubmitted() && !$form->isValid() ? 422 : 200
            )
        );
    }

    #[IsGranted('ROLE_USER')]
    public function password(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $dto = $this->manager->createDto($this->getUserOrThrow());

        $form = $this->handleForm($this->createForm(UserPasswordType::class, $dto), $dto, $request);
        if (!$form instanceof FormInterface) {
            return $form;
        }

        return $this->render(
            'user/settings/password.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(
                null,
                $form->isSubmitted() && !$form->isValid() ? 422 : 200
            )
        );
    }

    private function handleForm(
        FormInterface $form,
        UserDto $dto,
        Request $request
    ): FormInterface|Response {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->has('currentPassword')) {
            if (!$this->userPasswordHasher->isPasswordValid(
                $this->getUser(),
                $form->get('currentPassword')->getData()
            )) {
                $form->get('currentPassword')->addError(new FormError('Password is invalid'));
            }
        }

        if ($form->has('newEmail')) {
            $dto->email = $form->get('newEmail')->getData();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $this->getUser()->email;
            $this->manager->edit($this->getUser(), $dto);

            if ($dto->email !== $email || $dto->plainPassword) {
                $this->manager->logout();

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('user_settings_profile');
        }

        return $form;
    }
}
