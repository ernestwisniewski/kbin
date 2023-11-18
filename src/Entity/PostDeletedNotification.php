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
class PostDeletedNotification extends Notification
{
    #[ManyToOne(targetEntity: Post::class, inversedBy: 'notifications')]
    #[JoinColumn]
    public ?Post $post = null;

    public function __construct(User $receiver, Post $post)
    {
        parent::__construct($receiver);

        $this->post = $post;
    }

    public function getSubject(): Post
    {
        return $this->post;
    }

    public function getType(): string
    {
        return 'post_deleted_notification';
    }
}
