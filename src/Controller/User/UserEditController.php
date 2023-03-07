<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\DTO\UserDto;
use App\Form\UserBasicType;
use App\Form\UserEmailType;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserEditController extends AbstractController
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function general(UserManager $manager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $dto = $manager->createDto($this->getUserOrThrow());

        $basicForm = $this->handleForm($this->createForm(UserBasicType::class, $dto), $dto, $manager, $request);
        if (!$basicForm instanceof FormInterface) {
            return $basicForm;
        }

        return $this->render(
            'user/settings/profile.html.twig',
            [
                'form' => $basicForm->createView(),
            ],
            new Response(
                null,
                $basicForm->isSubmitted() && !$basicForm->isValid() ? 422 : 200
            )
        );
    }

    #[IsGranted('ROLE_USER')]
    public function email(UserManager $manager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $dto = $manager->createDto($this->getUserOrThrow());

        $basicForm = $this->handleForm($this->createForm(UserEmailType::class, $dto), $dto, $manager, $request);
        if (!$basicForm instanceof FormInterface) {
            return $basicForm;
        }

        return $this->render(
            'user/settings/email.html.twig',
            [
                'form' => $basicForm->createView(),
            ],
            new Response(
                null,
                $basicForm->isSubmitted() && !$basicForm->isValid() ? 422 : 200
            )
        );
    }

    private function handleForm(
        FormInterface $form,
        UserDto $dto,
        UserManager $manager,
        Request $request
    ): FormInterface|Response {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->has('currentPassword')) {
            $encodedPassword = $this->userPasswordHasher->hashPassword(
                $this->getUser(),
                $form->get('currentPassword')->getData()
            );

            if ($encodedPassword !== $this->getUser()->getPassword()) {
                $form->get('currentPassword')->addError(new FormError('Password is invalid'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $this->getUser()->email;
            $manager->edit($this->getUser(), $dto);

            if ($dto->email !== $email || $dto->plainPassword) {
                $manager->logout();

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('user_settings_profile');
        }

        return $form;
    }
}
