<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_entry_comment_vote_idx",
 *         columns={"user_id", "comment_id"}
 *     )
 * })
 * @ORM\Entity()
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="entryCommentVotes")
 * })
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 */
class EntryCommentVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="comment_id", nullable=false, onDelete="cascade")
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="votes")
     */
    public ?EntryComment $comment;

    public function __construct(int $choice, User $user, EntryComment $comment)
    {
        parent::__construct($choice, $user, $comment->user);

        $this->comment = $comment;
    }

    public function getComment(): EntryComment
    {
        return $this->comment;
    }

    public function setComment(?EntryComment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
