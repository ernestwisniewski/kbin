<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('login_socials')]
final class LoginSocialsComponent
{
    public function __construct(
        #[Autowire('%oauth_google_id%')]
        private readonly ?string $oauthGoogleId,
        #[Autowire('%oauth_facebook_id%')]
        private readonly ?string $oauthFacebookId,
        #[Autowire('%oauth_github_id%')]
        private readonly ?string $oauthGithubId,
        #[Autowire('%oauth_keycloak_id%')]
        private readonly ?string $oauthKeycloakId,
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

    public function githubEnabled(): bool
    {
        return !empty($this->oauthGithubId);
    }

    public function keycloakEnabled(): bool
    {
        return !empty($this->oauthKeycloakId);
    }
}
