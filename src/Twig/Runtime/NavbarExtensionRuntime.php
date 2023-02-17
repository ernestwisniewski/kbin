<?php

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

    public function navbarThreadsUrl(): string
    {
        $magazine = $this->requestStack->getCurrentRequest()->get('magazine');

        if ($magazine instanceof Magazine) {
            return $this->urlGenerator->generate('front_magazine', ['name' => $magazine->name]);
        }

        if ($domain = $this->requestStack->getCurrentRequest()->get('domain')) {
            return $this->urlGenerator->generate('domain_front', ['name' => $domain->name]);
        }

        if ($tag = $this->requestStack->getCurrentRequest()->get('tag')) {
            return $this->urlGenerator->generate('tag_overall', ['name' => $tag]);
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

    public function navbarPostsUrl(): string
    {
        $magazine = $this->requestStack->getCurrentRequest()->get('magazine');

        if ($magazine instanceof Magazine) {
            return $this->urlGenerator->generate('magazine_posts', ['name' => $magazine->name]);
        }

        if ($tag = $this->requestStack->getCurrentRequest()->get('tag')) {
            return $this->urlGenerator->generate('tag_posts_front', ['name' => $tag]);
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

    private function getCurrentRouteName(): string
    {
        return $this->requestStack->getCurrentRequest()->get('_route') ?? 'front';
    }
}
