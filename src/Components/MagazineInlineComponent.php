<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Magazine;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('magazine_inline')]
class MagazineInlineComponent
{
    public Magazine $magazine;
    public bool $prefix = true;
    public bool $bolded = false;
}
