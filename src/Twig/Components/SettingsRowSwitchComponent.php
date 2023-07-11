<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent ('settings_row_switch', template: 'components/_settings_row_switch.html.twig')]
class SettingsRowSwitchComponent
{
    public string $title;
    public string $description;
    public string $settingsKey;
    public bool $defaultValue = false;
}