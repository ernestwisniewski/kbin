<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Kbin\User\DTO\UserDto;
use App\Kbin\User\Factory\UserFactory;
use App\Kbin\User\Form\UserBasicType;
use App\Kbin\User\Form\UserEmailType;
use App\Kbin\User\Form\UserPasswordType;
use App\Kbin\User\UserEdit;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserEditController extends AbstractController
{
    public function __construct(
        private readonly UserEdit $userEdit,
        private readonly UserFactory $userFactory,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function general(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $dto = $this->userFactory->createDto($this->getUserOrThrow());

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

        $dto = $this->userFactory->createDto($this->getUserOrThrow());

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

        $dto = $this->userFactory->createDto($this->getUserOrThrow());

        $form = $this->handleForm($this->createForm(UserPasswordType::class, $dto), $dto, $request);
        if (!$form instanceof FormInterface) {
            return $form;
        }

        return $this->render(
            'user/settings/password.html.twig',
            [
                'form' => $form->createView(),
                'has2fa' => $this->getUserOrThrow()->isTotpAuthenticationEnabled(),
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
                $form->get('currentPassword')->addError(new FormError($this->translator->trans('Password is invalid')));
            }
        }

        if ($form->has('newEmail')) {
            $dto->email = $form->get('newEmail')->getData();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $this->getUser()->email;
            ($this->userEdit)($this->getUser(), $dto);

            if ($dto->email !== $email || $dto->plainPassword) {
                $this->security->logout(false);

                $this->addFlash('success', 'account_settings_changed');

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('user_settings_profile');
        }

        return $form;
    }
}
