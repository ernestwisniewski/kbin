<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class MagazineLogPostCommentDeleted extends MagazineLog
{
    #[ManyToOne(targetEntity: PostComment::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    public ?PostComment $postComment = null;

    public function __construct(PostComment $comment, User $user)
    {
        parent::__construct($comment->magazine, $user);

        $this->postComment = $comment;
    }

    public function getType(): string
    {
        return 'log_post_comment_deleted';
    }

    public function getComment(): PostComment
    {
        return $this->postComment;
    }

    public function getSubject(): ContentInterface
    {
        return $this->postComment;
    }

    public function clearSubject(): MagazineLog
    {
        $this->postComment = null;

        return $this;
    }
}
