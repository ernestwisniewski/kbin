<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\MessageBus;

use App\Entity\User;
use App\Kbin\MessageBus\Contracts\SendConfirmationEmailInterface;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\SettingsManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
readonly class UserConfirmationEmailSentHandler
{
    public function __construct(
        private SettingsManager $settingsManager,
        private EmailVerifier $emailVerifier,
        private UserRepository $repository,
        private ParameterBagInterface $params,
        private TranslatorInterface $translator
    ) {
    }

    public function __invoke(SendConfirmationEmailInterface $message)
    {
        $user = $this->repository->find($message->userId);
        if (!$user) {
            throw new UnrecoverableMessageHandlingException('User not found');
        }

        $this->sendConfirmationEmail($user);
    }

    /**
     * @param User $user user that will be sent the confirmation email
     *
     * @return void
     */
    public function sendConfirmationEmail(User $user)
    {
        try {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(
                        new Address($this->settingsManager->get('KBIN_SENDER_EMAIL'), $this->params->get('kbin_domain'))
                    )
                    ->to($user->email)
                    ->subject($this->translator->trans('email_confirm_title'))
                    ->htmlTemplate('_email/confirmation_email.html.twig')
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
