<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Entry;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_cross', template: 'components/entry_cross.html.twig')]
final class EntryCrossComponent
{
    public ?Entry $entry;
}
