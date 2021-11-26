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
 * @ORM\Entity()
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="postVotes")
 * })
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 */
class PostVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="post_id", nullable=false, onDelete="cascade")
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="votes")
     */
    public ?Post $post;

    public function __construct(int $choice, User $user, ?Post $post)
    {
        parent::__construct($choice, $user, $post->user);

        $this->post = $post;
    }
}
