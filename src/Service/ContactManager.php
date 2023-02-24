<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ContactDto;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactManager
{
    public function __construct(
        private readonly SettingsManager $settings,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly RateLimiterFactory $contactLimiter
    ) {
    }

    public function send(ContactDto $dto)
    {
        $limiter = $this->contactLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->settings->get('KBIN_SENDER_EMAIL'), $this->settings->get('KBIN_DOMAIN')))
            ->to($this->settings->get('KBIN_CONTACT_EMAIL'))
            ->subject($this->translator->trans('contact').' - '.$this->settings->get('KBIN_DOMAIN'))
            ->htmlTemplate('_email/contact.html.twig')
            ->context([
                'name' => $dto->name,
                'senderEmail' => $dto->email,
                'message' => $dto->message,
            ]);

        $this->mailer->send($email);
    }
}
