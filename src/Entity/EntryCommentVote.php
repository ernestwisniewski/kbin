<?php declare(strict_types = 1);

namespace App\Entity;

use App\Repository\EntryCommentVoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_entry_comment_vote_idx",
 *         columns={"user_id", "comment_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass="App\Repository\EntryCommentVoteRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="entryCommentVotes")
 * })
 */
class EntryCommentVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="comment_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="votes")
     */
    private ?EntryComment $comment;

    public function __construct(int $choice, User $user, EntryComment $comment)
    {
        parent::__construct($choice, $user);

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
