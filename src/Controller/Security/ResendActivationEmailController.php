<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\Form\ResendEmailActivationFormType;
use App\MessageHandler\SentUserConfirmationEmailHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResendActivationEmailController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function resend(Request $request, SentUserConfirmationEmailHandler $confirmationHandler): Response
    {
        $form = $this->createForm(ResendEmailActivationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $email,
            ]);

            if (\is_null($user) || $user->isVerified) {
                $this->addFlash('error', 'resend_account_activation_email_error');

                return $this->redirectToRoute('app_resend_email_activation');
            }

            try {
                // send confirmation email to user
                $confirmationHandler->sendConfirmationEmail($user);
                $this->addFlash('success', 'resend_account_activation_email_success');

                return $this->redirectToRoute('app_resend_email_activation');
            } catch (\Exception $e) {
                $this->addFlash('error', 'resend_account_activation_email_error');

                return $this->redirectToRoute('app_resend_email_activation');
            }
        }

        return $this->render('resend_verification_email/resend.html.twig', [
            'form' => $form,
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}
