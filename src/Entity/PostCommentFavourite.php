<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
class PostCommentFavourite extends Favourite
{
    #[ManyToOne(targetEntity: PostComment::class, inversedBy: 'favourites')]
    #[JoinColumn(nullable: true)]
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
