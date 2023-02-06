<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\ContextExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContextExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_route_name', [ContextExtensionRuntime::class, 'isRouteName']),
            new TwigFunction('is_route_name_contains', [ContextExtensionRuntime::class, 'isRouteNameContains']),
            new TwigFunction('route_has_param', [ContextExtensionRuntime::class, 'routeHasParam']),
        ];
    }
}
