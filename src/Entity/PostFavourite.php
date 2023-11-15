<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
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
