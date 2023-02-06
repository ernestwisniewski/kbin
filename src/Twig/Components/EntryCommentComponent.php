<?php

namespace App\Twig\Components;

use App\Entity\EntryComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment')]
final class EntryCommentComponent
{
        public EntryComment $comment;
}
