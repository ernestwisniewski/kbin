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
class PostFavourite extends Favourite
{
    #[ManyToOne(targetEntity: Post::class, inversedBy: 'favourites')]
    #[JoinColumn]
    public ?Post $post = null;

    public function __construct(User $user, Post $post)
    {
        parent::__construct($user);

        $this->magazine = $post->magazine;
        $this->post = $post;
    }

    public function getSubject(): Post
    {
        return $this->post;
    }

    public function clearSubject(): Favourite
    {
        $this->post = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post';
    }
}
