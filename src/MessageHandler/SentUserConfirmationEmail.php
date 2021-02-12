<?php declare(strict_types=1);

namespace App\MessageHandler;

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

    public function __invoke(UserCreatedMessage $entryCreatedMessage)
    {
        $user = $this->userRepository->find($entryCreatedMessage->getUserId());

        if (!$user) {
            return;
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@karab.in', 'karab.in Bot'))
                ->to($user->getEmail())
                ->subject('Potwierdź swój adres email')
                ->htmlTemplate('_email/confirmation_email.html.twig')
        );
    }
}
