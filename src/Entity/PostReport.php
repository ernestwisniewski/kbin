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
class PostReport extends Report
{
    #[ManyToOne(targetEntity: Post::class, inversedBy: 'reports')]
    #[JoinColumn]
    public ?Post $post = null;

    public function __construct(User $reporting, Post $post, string $reason = null)
    {
        parent::__construct($reporting, $post->user, $post->magazine, $reason);

        $this->post = $post;
    }

    public function getSubject(): Post
    {
        return $this->post;
    }

    public function clearSubject(): Report
    {
        $this->post = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post';
    }
}
