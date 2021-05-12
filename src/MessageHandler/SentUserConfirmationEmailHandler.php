<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\Contracts\SendConfirmationEmailInterface;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;

class SentUserConfirmationEmailHandler implements MessageHandlerInterface
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private UserRepository $repository
    ) {
    }

    public function __invoke(SendConfirmationEmailInterface $message)
    {
        $user = $this->repository->find($message->userId);

        if (!$user) {
            throw new UnrecoverableMessageHandlingException('User not found');
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
