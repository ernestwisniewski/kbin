<?php

namespace App\Twig\Runtime;

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
        if ($magazine = $this->requestStack->getCurrentRequest()->get('magazine')) {
            return $this->entriesMagazine($magazine->name);
        }

        return $this->front();
    }

    public function navbarPostsUrl(): string
    {
        if ($magazine = $this->requestStack->getCurrentRequest()->get('magazine')) {
            return $this->postsMagazine($magazine->name);
        }

        return $this->posts();
    }

    private function front(): string
    {
        return $this->urlGenerator->generate('front');
    }

    private function entriesMagazine(string $name): string
    {
        return $this->urlGenerator->generate('front_magazine', ['name' => $name]);
    }

    private function posts(): string
    {
        return $this->urlGenerator->generate('posts_front');
    }

    private function postsMagazine(string $name): string
    {
        return $this->urlGenerator->generate('magazine_posts', ['name' => $name]);
    }

    private function getCurrentRouteName(): string
    {
        return $this->requestStack->getCurrentRequest()->get('_route') ?? 'front';
    }
}
