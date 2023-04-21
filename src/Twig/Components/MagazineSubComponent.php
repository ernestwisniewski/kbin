<?php

namespace App\Twig\Components;

use App\Entity\Magazine;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('magazine_sub')]
final class MagazineSubComponent
{
    public Magazine $magazine;
}
