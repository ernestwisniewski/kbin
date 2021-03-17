<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_post_vote_idx",
 *         columns={"user_id", "post_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass="App\Repository\PostVoteRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="postVotes")
 * })
 */
class PostVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="post_id", nullable=false, onDelete="cascade")
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="votes")
     */
    private ?Post $post;

    public function __construct(int $choice, User $user, ?Post $post)
    {
        parent::__construct($choice, $user, $post->getUser());

        $this->post = $post;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }
}
