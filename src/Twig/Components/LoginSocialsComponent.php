<?php

namespace App\Twig\Components;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('login_socials')]
final class LoginSocialsComponent
{
    public function __construct(
        #[Autowire(env: 'OAUTH_GOOGLE_ID')]
        private readonly string $oauthGoogleId,
        #[Autowire(env: 'OAUTH_FACEBOOK_ID')]
        private readonly string $oauthFacebookId,
        #[Autowire(env: 'OAUTH_KEYCLOAK_ID')]
        private readonly string $oauthKeycloakId,
    ) {
    }

    public function googleEnabled(): bool
    {
        return !empty($this->oauthGoogleId);
    }

    public function facebookEnabled(): bool
    {
        return !empty($this->oauthFacebookId);
    }

    public function keycloakEnabled(): bool
    {
        return !empty($this->oauthKeycloakId);
    }
}
