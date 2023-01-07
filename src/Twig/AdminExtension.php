<?php

declare(strict_types=1);

namespace App\Twig;

use App\Twig\Runtime\AdminRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AdminExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_admin_panel_page', [AdminRuntime::class, 'isAdminPanelPage']),
        ];
    }
}
