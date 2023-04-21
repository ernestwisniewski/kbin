<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\AdminExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AdminExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_admin_panel_page', [AdminExtensionRuntime::class, 'isAdminPanelPage']),
        ];
    }
}
