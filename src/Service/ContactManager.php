<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactManager
{
    public function __construct(
        private string $kbinDomain,
        private string $kbinContactEmail,
        private MailerInterface $mailer,
        private TranslatorInterface $translator
    ) {
    }

    public function send(string $name, string $senderEmail, string $message)
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@mg.karab.in ', $this->kbinDomain))
            ->to($this->kbinContactEmail)
            ->subject($this->translator->trans('contact') . ' - ' . $this->kbinDomain)
            ->htmlTemplate('_email/contact.html.twig')
            ->context(compact('name', 'senderEmail', 'message'));

        $this->mailer->send($email);
    }
}
