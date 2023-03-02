<?php

namespace App\Twig\Components;

use App\Entity\EntryComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment')]
final class EntryCommentComponent
{
    public EntryComment $comment;
    public bool $showMagazineName = true;
    public bool $showEntryTitle = true;
    public bool $showNested = false;
    public int $level = 1;

    public function getLevel(): int
    {
        return min($this->level, 10);
    }
}
