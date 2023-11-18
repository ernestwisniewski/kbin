<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AuthorizationCodeSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    private Security $security;
    private UrlGeneratorInterface $urlGenerator;
    private RequestStack $requestStack;

    public function __construct(Security $security, UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public function onLeagueOauth2ServerEventAuthorizationRequestResolve(AuthorizationRequestResolveEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security->getUser();
        $firewallName = $this->security->getFirewallConfig($request)->getName();

        $this->saveTargetPath($request->getSession(), $firewallName, $request->getUri());
        $response = new RedirectResponse($this->urlGenerator->generate('app_login'), 307);
        if ($user instanceof UserInterface) {
            if (null !== $request->getSession()->get('consent_granted')) {
                $event->resolveAuthorization($request->getSession()->get('consent_granted'));
                $request->getSession()->remove('consent_granted');

                return;
            }
            $response = new RedirectResponse($this->urlGenerator->generate('app_consent', $request->query->all()), 307);
        }
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'league.oauth2_server.event.authorization_request_resolve' => 'onLeagueOauth2ServerEventAuthorizationRequestResolve',
        ];
    }
}
