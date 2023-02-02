<?php

namespace App\Twig\Components;

use App\Entity\Entry;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry')]
final class EntryComponent
{
    public Entry $entry;
    public bool $isSingle = false;

}
