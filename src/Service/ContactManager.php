<?php declare(strict_types=1);

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
        private string $kbinDomain,
        private string $kbinContactEmail,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private RateLimiterFactory $contactLimiter
    ) {
    }

    public function send(ContactDto $dto)
    {
        $limiter = $this->contactLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@mg.karab.in ', $this->kbinDomain))
            ->to($this->kbinContactEmail)
            ->subject($this->translator->trans('contact').' - '.$this->kbinDomain)
            ->htmlTemplate('_email/contact.html.twig')
            ->context([
                'name'        => $dto->name,
                'senderEmail' => $dto->email,
                'message'     => $dto->message,
            ]);

        $this->mailer->send($email);
    }
}
