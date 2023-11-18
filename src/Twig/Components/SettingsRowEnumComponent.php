<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('settings_row_enum', template: 'components/_settings_row_enum.html.twig')]
class SettingsRowEnumComponent
{
    public string $label;
    public string $help = '';
    public string $settingsKey;
    public array $values;
    public ?string $defaultValue = null;
    public bool $reloadRequired = true;
}
