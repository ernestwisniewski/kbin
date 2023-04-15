<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\Contracts\SendConfirmationEmailInterface;
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
class SentUserConfirmationEmailHandler
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly EmailVerifier $emailVerifier,
        private readonly UserRepository $repository,
        private readonly ParameterBagInterface $params,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(SendConfirmationEmailInterface $message)
    {
        $user = $this->repository->find($message->userId);

        if (!$user) {
            throw new UnrecoverableMessageHandlingException('User not found');
        }

        // @todo
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
        }
    }
}
