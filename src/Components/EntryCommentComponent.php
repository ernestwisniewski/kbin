<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\EntryComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment')]
class EntryCommentComponent
{
    public EntryComment $comment;
    public ?string $extraClass = null;
    public int $level = 1;
    public bool $withParent = false;
    public bool $nested = true;
    public bool $showMagazine = false;
}
