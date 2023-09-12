<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Magazine;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('magazine_inline')]
final class MagazineInlineComponent
{
    public Magazine $magazine;
    public bool $showTitle = true;
    public bool $fullName = false;
    public bool $stretchedLink = false;
    public bool $showAvatar = false;
}
