<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class EntryCommentDeletedEvent
{
    public function __construct(public EntryComment $comment, public ?User $user = null)
    {
    }
}
