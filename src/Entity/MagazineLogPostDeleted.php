<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class MagazineLogPostDeleted extends MagazineLog
{
    #[ManyToOne(targetEntity: Post::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    public ?Post $post;

    public function __construct(Post $post, User $user)
    {
        parent::__construct($post->magazine, $user);

        $this->post = $post;
    }

    public function getType(): string
    {
        return 'log_post_deleted';
    }

    public function getSubject(): ContentInterface
    {
        return $this->post;
    }

    public function clearSubject(): MagazineLog
    {
        $this->post = null;

        return $this;
    }
}
