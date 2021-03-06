<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\Contracts\SendConfirmationEmailInterface;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\UserCreatedMessage;
use Symfony\Component\Mime\Address;

class SentUserConfirmationEmail implements MessageHandlerInterface
{
    private EmailVerifier $emailVerifier;
    private UserRepository $userRepository;

    public function __construct(EmailVerifier $emailVerifier, UserRepository $userRepository)
    {
        $this->emailVerifier  = $emailVerifier;
        $this->userRepository = $userRepository;
    }

    public function __invoke(SendConfirmationEmailInterface $entryCreatedMessage)
    {
        $user = $this->userRepository->find($entryCreatedMessage->getUserId());

        if (!$user) {
            return;
        }

        //@todo
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@mg.karab.in ', 'karab.in Bot'))
                ->to($user->getEmail())
                ->subject('Potwierdź swój adres email')
                ->htmlTemplate('_email/confirmation_email.html.twig')
        );
    }
}
