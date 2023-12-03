<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\VotableInterface;
use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\AssociationOverrides;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table]
#[UniqueConstraint(name: 'user_post_comment_vote_idx', columns: ['user_id', 'comment_id'])]
#[AssociationOverrides([
    new AssociationOverride(name: 'user', inversedBy: 'postCommentVotes'),
])]
class PostCommentVote extends Vote
{
    #[ManyToOne(targetEntity: PostComment::class, inversedBy: 'votes')]
    #[JoinColumn(name: 'comment_id', nullable: false, onDelete: 'CASCADE')]
    public ?PostComment $comment = null;

    public function __construct(int $choice, User $user, PostComment $comment)
    {
        parent::__construct($choice, $user, $comment->user);

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

    public function getSubject(): VotableInterface
    {
        return $this->comment;
    }
}
