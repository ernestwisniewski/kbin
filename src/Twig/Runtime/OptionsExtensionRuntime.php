<?php

namespace App\Twig\Runtime;

use App\Repository\EntryRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class OptionsExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function optionsUrl(string $name, string $value): string
    {
        $route = $this->requestStack->getCurrentRequest()->attributes->get('_route');
        $params = $this->requestStack->getCurrentRequest()->attributes->all()['_route_params'];

        $params[$name] = $value;

        return $this->urlGenerator->generate($route, $params);
    }

    public function options_is_active(string $name, string $value): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $params = $request->attributes->all()['_route_params'];

        if (!key_exists($name, $params)) {
            return false;
        }

        if ($value === $params[$name]) {
            return true;
        }

        if (null === $params[$name] && EntryRepository::SORT_DEFAULT == $value) {
            return true;
        }

        return false;
    }
}
