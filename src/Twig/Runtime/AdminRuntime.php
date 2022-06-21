<?php declare(strict_types = 1);

namespace App\Twig\Runtime;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class AdminRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function isAdminPanelPage(): bool
    {
        return str_starts_with($this->getCurrentRouteName(), 'admin');
    }

    private function getCurrentRouteName(): string
    {
        return $this->getCurrentRequest()->get('_route') ?? 'front';
    }

    private function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
