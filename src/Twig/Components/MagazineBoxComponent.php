<?php

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
}
