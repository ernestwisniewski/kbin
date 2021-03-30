<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Repository\MagazineLogEntryDeleteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MagazineLogEntryCommentDeleteRepository")
 */
class MagazineLogEntryCommentDelete extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?EntryComment $entryComment;

    public function __construct(EntryComment $comment, User $user)
    {
        parent::__construct($comment->getMagazine(), $user);

        $this->entryComment = $comment;
    }

    public function getType(): string
    {
        return 'log_entry_comment_delete';
    }

    public function getComment(): EntryComment
    {
        return $this->entryComment;
    }

    public function getSubject(): ContentInterface
    {
        return $this->entryComment;
    }

    public function clearSubject(): MagazineLog
    {
        $this->entryComment = null;

        return $this;
    }
}
