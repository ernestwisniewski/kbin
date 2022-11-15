<?php declare(strict_types=1);

namespace App\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('title')]
class TitleComponent
{
    public string $title;
    public ?string $url = null;
}
