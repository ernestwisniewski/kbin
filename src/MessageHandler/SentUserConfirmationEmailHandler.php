<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\Contracts\SendConfirmationEmailInterface;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class SentUserConfirmationEmailHandler implements MessageHandlerInterface
{
    public function __construct(
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

        //@todo
        try {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@mg.karab.in ', $this->params->get('kbin_domain')))
                    ->to($user->email)
                    ->subject($this->translator->trans('email_confirm_title'))
                    ->htmlTemplate('_email/confirmation_email.html.twig')
            );
        } catch (\Exception $e) {
        }
    }
}
