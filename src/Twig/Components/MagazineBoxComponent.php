<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Magazine;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('magazine_box')]
final class MagazineBoxComponent
{
    public Magazine $magazine;
    public bool $showCover = true;
    public bool $showDescription = true;
    public bool $showRules = true;
    public bool $showSubscribeButton = true;
    public bool $showInfo = true;
    public bool $showMeta = true;
    public bool $showSectionTitle = false;
    public bool $stretchedLink = true;
}
