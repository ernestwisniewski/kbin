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
class PostCommentReport extends Report
{
    #[ManyToOne(targetEntity: PostComment::class, inversedBy: 'reports')]
    #[JoinColumn]
    public ?PostComment $postComment = null;

    public function __construct(User $reporting, PostComment $comment, string $reason = null)
    {
        parent::__construct($reporting, $comment->user, $comment->magazine, $reason);

        $this->postComment = $comment;
    }

    public function getSubject(): PostComment
    {
        return $this->postComment;
    }

    public function clearSubject(): Report
    {
        $this->postComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post_comment';
    }
}
