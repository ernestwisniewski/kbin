<?php

namespace App\Twig\Components;

use App\Entity\Entry;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('entry')]
final class EntryComponent
{
    public ?Entry $entry;
    public bool $isSingle = false;
    public bool $showShortSentence = true;
    public bool $showBody = false;
    public bool $showMagazineName = true;

    #[PostMount]
    public function postMount(array $attr): array
    {
        if ($this->isSingle) {
            $this->showMagazineName = false;

            if (isset($attr['class'])) {
                $attr['class'] = trim('entry--single '.$attr['class']);
            } else {
                $attr['class'] = 'entry--single';
            }
        }

        return $attr;
    }
}
