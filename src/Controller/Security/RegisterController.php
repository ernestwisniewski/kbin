<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use App\Kbin\User\Form\UserRegisterType;
use App\Kbin\User\UserCreate;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends AbstractController
{
    public function __invoke(
        UserCreate $userCreate,
        Request $request,
        IpResolver $ipResolver
    ): Response {
        if ($this->getParameter('sso_only_mode')) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('front_subscribed');
        }

        $form = $this->createForm(UserRegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $dto->ip = $ipResolver->resolve();

            $userCreate($dto);

            $this->addFlash(
                'success',
                'flash_register_success'
            );

            return $this->redirectToRoute('front');
        }

        return $this->render(
            'user/register.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
