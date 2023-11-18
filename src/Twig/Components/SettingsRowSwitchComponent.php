<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('settings_row_switch', template: 'components/_settings_row_switch.html.twig')]
class SettingsRowSwitchComponent
{
    public string $label;
    public string $help = '';
    public string $settingsKey;
    public bool $defaultValue = false;
    public bool $reloadRequired = true;
}
