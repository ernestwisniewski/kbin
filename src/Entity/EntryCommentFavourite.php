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
class EntryCommentFavourite extends Favourite
{
    #[ManyToOne(targetEntity: EntryComment::class, inversedBy: 'favourites')]
    #[JoinColumn(onDelete: 'SET NULL')]
    public ?EntryComment $entryComment = null;

    public function __construct(User $user, EntryComment $comment)
    {
        parent::__construct($user);

        $this->magazine = $comment->magazine;
        $this->entryComment = $comment;
    }

    public function getSubject(): EntryComment
    {
        return $this->entryComment;
    }

    public function clearSubject(): Favourite
    {
        $this->entryComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry_comment';
    }
}
