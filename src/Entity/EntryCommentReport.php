<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class EntryCommentReport extends Report
{
    #[ManyToOne(targetEntity: EntryComment::class, inversedBy: 'reports')]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?EntryComment $entryComment = null;

    public function __construct(User $reporting, EntryComment $comment, ?string $reason = null)
    {
        parent::__construct($reporting, $comment->user, $comment->magazine, $reason);

        $this->entryComment = $comment;
    }

    public function getSubject(): EntryComment
    {
        return $this->entryComment;
    }

    public function clearSubject(): Report
    {
        $this->entryComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry_comment';
    }
}
