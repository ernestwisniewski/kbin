<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\IpResolver;
use App\Service\SettingsManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class UserDeleteRequestController extends AbstractController
{
    public function __construct(
        private readonly SettingsManager $settings,
        private readonly MailerInterface $mailer,
        private readonly RateLimiterFactory $contactLimiter,
        private readonly IpResolver $ipResolver
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(User $user, UserManager $manager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $this->validateCsrf('user_delete', $request->request->get('token'));

        $limiter = $this->contactLimiter->create($this->ipResolver->resolve());
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->settings->get('KBIN_SENDER_EMAIL'), $this->settings->get('KBIN_DOMAIN')))
            ->to($this->settings->get('KBIN_CONTACT_EMAIL'))
            ->subject('User Delete Account Request')
            ->htmlTemplate('_email/delete_account_request.html.twig')
            ->context([
                'username' => $this->getUser()->getUsername(),
                'mail' => $this->getUser()->getEmail(),
            ]);

        $this->mailer->send($email);

        $this->addFlash('success', 'delete_account_request_send');

        return $this->redirectToRoute('front');
    }
}
