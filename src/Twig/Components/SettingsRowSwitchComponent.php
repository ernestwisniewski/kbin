<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent ('settings_row_switch', template: 'components/_settings_row_switch.html.twig')]
class SettingsRowSwitchComponent
{
    public string $label;
    public string $help = '';
    public string $settingsKey;
    public bool $defaultValue = false;
}