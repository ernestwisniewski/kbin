<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\DTO;

use App\DTO\ImageDto;
use App\Entity\Magazine;

class MagazineThemeDto
{
    public ?Magazine $magazine = null;
    public ?ImageDto $icon = null;
    public ?string $customCss = null;
    public ?string $customJs = null;
    public ?string $primaryColor = null;
    public ?string $primaryDarkerColor = null;
    public ?string $backgroundImage = null;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
        $this->customCss = $magazine->customCss;
    }

    public function create(?ImageDto $icon)
    {
        $this->icon = $icon;
    }
}
