<?php declare(strict_types = 1);

namespace App\Entity;

use App\Repository\PostCommentVoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_post_comment_vote_idx",
 *         columns={"user_id", "comment_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass="App\Repository\PostCommentVoteRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="postCommentVotes")
 * })
 */
class PostCommentVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="comment_id", nullable=false, onDelete="cascade")
     * @ORM\ManyToOne(targetEntity="PostComment", inversedBy="votes")
     */
    private ?PostComment $comment;

    public function __construct(int $choice, User $user, PostComment $comment)
    {
        parent::__construct($choice, $user);

        $this->comment = $comment;
    }

    public function getComment(): PostComment
    {
        return $this->comment;
    }

    public function setComment(?PostComment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
