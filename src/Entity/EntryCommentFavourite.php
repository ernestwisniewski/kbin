<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryCommentFavourite extends Favourite
{
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="favourites")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?EntryComment $entryComment;

    public function __construct(User $user, EntryComment $comment)
    {
        parent::__construct($user);

        $this->magazine     = $comment->magazine;
        $this->entryComment = $comment;
    }

    public function getSubject(): EntryComment
    {
        return $this->entryComment;
    }

    public function clearSubject(): Favourite
    {
        $this->entryComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry_comment';
    }
}
