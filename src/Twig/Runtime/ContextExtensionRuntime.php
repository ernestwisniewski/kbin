<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class ContextExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function isRouteNameContains(string $needle): bool
    {
        return str_contains($this->getCurrentRouteName(), $needle);
    }

    public function isRouteName(string $needle): bool
    {
        return $this->getCurrentRouteName() === $needle;
    }

    public function routeHasParam(string $name, string $needle): bool
    {
        return $this->requestStack->getCurrentRequest()->get($name) === $needle;
    }

    private function getCurrentRouteName(): string
    {
        return $this->requestStack->getCurrentRequest()->get('_route') ?? 'front';
    }
}
