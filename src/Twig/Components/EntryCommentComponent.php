<?php

namespace App\Twig\Components;

use App\Entity\EntryComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment')]
final class EntryCommentComponent
{
    const SHOW_ENTRY_TITLE = true;

    public EntryComment $comment;
    public bool $showMagazineName = true;
    public bool $showEntryTitle = self::SHOW_ENTRY_TITLE;
}
