<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class PostCommentFavourite extends Favourite
{
    #[ManyToOne(targetEntity: PostComment::class, inversedBy: 'favourites')]
    #[JoinColumn]
    public ?PostComment $postComment = null;

    public function __construct(User $user, PostComment $comment)
    {
        parent::__construct($user);

        $this->magazine = $comment->magazine;
        $this->postComment = $comment;
    }

    public function getSubject(): PostComment
    {
        return $this->postComment;
    }

    public function clearSubject(): Favourite
    {
        $this->postComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post_comment';
    }
}
