<?php

namespace App\Entity;

use App\Repository\EntryCommentVoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EntryVoteRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="entryCommentVotes")
 * })
 */
class EntryCommentVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="entry_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="votes")
     */
    private EntryComment $comment;

    public function __construct(int $choice, User $user, EntryComment $comment)
    {
        parent::__construct($choice, $user);

        $this->comment = $comment;
    }

    public function getComment(): EntryComment
    {
        return $this->comment;
    }

    public function setComment(EntryComment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

}
