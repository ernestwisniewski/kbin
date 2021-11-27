<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\DTO\UserDto;
use App\Form\UserBasicType;
use App\Form\UserEmailType;
use App\Form\UserPasswordType;
use App\Service\UserManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserEditController extends AbstractController
{

    public function __invoke(UserManager $manager, Request $request): Response
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
}
