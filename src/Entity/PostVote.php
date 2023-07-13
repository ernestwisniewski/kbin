<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\VotableInterface;
use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\AssociationOverrides;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table]
#[UniqueConstraint(name: 'user_post_vote_idx', columns: ['user_id', 'post_id'])]
#[AssociationOverrides([
    new AssociationOverride(name: 'user', inversedBy: 'postVotes'),
])]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
class PostVote extends Vote
{
    #[ManyToOne(targetEntity: Post::class, inversedBy: 'votes')]
    #[JoinColumn(name: 'post_id', nullable: false, onDelete: 'CASCADE')]
    public ?Post $post = null;

    public function __construct(int $choice, User $user, ?Post $post)
    {
        parent::__construct($choice, $user, $post->user);

        $this->post = $post;
    }

    public function getSubject(): VotableInterface
    {
        return $this->post;
    }
}
