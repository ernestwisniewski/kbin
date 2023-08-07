<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private readonly ClientRegistry $clientRegistry,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        $user = $token->getUser();

        if (null !== $user->oauthKeycloakId) {
            $event->setResponse(
                new RedirectResponse(
                    $this->clientRegistry->getClient('keycloak')->getOAuth2Provider()->getLogoutUrl()
                )
            );
        }
    }
}
