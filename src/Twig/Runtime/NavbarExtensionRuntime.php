<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Magazine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class NavbarExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function navbarThreadsUrl(?Magazine $magazine): string
    {
        if ($magazine instanceof Magazine) {
            return $this->urlGenerator->generate('front_magazine', ['name' => $magazine->name]);
        }

        if ($domain = $this->requestStack->getCurrentRequest()->get('domain')) {
            return $this->urlGenerator->generate('domain_entries', ['name' => $domain->name]);
        }

        if (str_starts_with($this->getCurrentRouteName(), 'tag')) {
            return $this->urlGenerator->generate(
                'tag_entries',
                ['name' => $this->requestStack->getCurrentRequest()->get('name')]
            );
        }

        if (str_ends_with($this->getCurrentRouteName(), '_subscribed')) {
            return $this->urlGenerator->generate('front_subscribed');
        }

        if (str_ends_with($this->getCurrentRouteName(), '_favourite')) {
            return $this->urlGenerator->generate('front_favourite');
        }

        if (str_ends_with($this->getCurrentRouteName(), '_moderated')) {
            return $this->urlGenerator->generate('front_moderated');
        }

        return $this->urlGenerator->generate('front');
    }

    public function navbarPostsUrl(?Magazine $magazine): string
    {
        if ($magazine instanceof Magazine) {
            return $this->urlGenerator->generate('magazine_posts', ['name' => $magazine->name]);
        }

        if (str_starts_with($this->getCurrentRouteName(), 'tag')) {
            return $this->urlGenerator->generate(
                'tag_posts',
                ['name' => $this->requestStack->getCurrentRequest()->get('name')]
            );
        }

        if (str_ends_with($this->getCurrentRouteName(), '_subscribed')) {
            return $this->urlGenerator->generate('posts_subscribed');
        }

        if (str_ends_with($this->getCurrentRouteName(), '_favourite')) {
            return $this->urlGenerator->generate('posts_favourite');
        }

        if (str_ends_with($this->getCurrentRouteName(), '_moderated')) {
            return $this->urlGenerator->generate('posts_moderated');
        }

        return $this->urlGenerator->generate('posts_front');
    }

    public function navbarPeopleUrl(?Magazine $magazine): string
    {
        if (str_starts_with($this->getCurrentRouteName(), 'tag')) {
            return $this->urlGenerator->generate(
                'tag_people',
                ['name' => $this->requestStack->getCurrentRequest()->get('name')]
            );
        }

        if ($magazine instanceof Magazine) {
            return $this->urlGenerator->generate('magazine_people', ['name' => $magazine->name]);
        }

        return $this->urlGenerator->generate('people_front');
    }

    private function getCurrentRouteName(): string
    {
        return $this->requestStack->getCurrentRequest()->get('_route') ?? 'front';
    }
}
