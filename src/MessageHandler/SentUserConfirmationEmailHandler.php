<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\Contracts\SendConfirmationEmailInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;

class SentUserConfirmationEmailHandler implements MessageHandlerInterface
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private UserRepository $repository
    ) {
    }

    public function __invoke(SendConfirmationEmailInterface $entryCreatedMessage)
    {
        $user = $this->repository->find($entryCreatedMessage->userId);

        if (!$user) {
            return;
        }

        //@todo
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@mg.karab.in ', 'karab.in Bot'))
                ->to($user->email)
                ->subject('Potwierdź swój adres email')
                ->htmlTemplate('_email/confirmation_email.html.twig')
        );
    }
}
