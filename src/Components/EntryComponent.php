<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Entry;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry')]
class EntryComponent
{
    public Entry $entry;
    public string $titleTag = 'h4';
    public ?string $extraClass = null;
    public bool $showContent = false;
    public bool $directUrl = false;
    public bool $showMagazine = true;
}
