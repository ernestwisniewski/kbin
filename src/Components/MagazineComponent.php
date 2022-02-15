<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Magazine;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('magazine')]
class MagazineComponent
{
    public Magazine $magazine;
    public bool $showCover = true;
    public bool $showDescription = true;
    public bool $showRules = true;
    public bool $showInfo = true;
    public bool $showStats = true;
    public bool $showSubButtons = true;
    public bool $asLink = false;
}
