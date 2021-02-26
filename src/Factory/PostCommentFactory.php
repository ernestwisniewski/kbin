<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\PostCommentDto;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\PostComment;
use App\Entity\User;

class PostCommentFactory
{
    public function createFromDto(PostCommentDto $commentDto, User $user): PostComment
    {
        return new PostComment(
            $commentDto->getBody(),
            $commentDto->getPost(),
            $user,
            $commentDto->getParent()
        );
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return (new PostCommentDto())->create(
            $comment->getPost(),
            $comment->getBody(),
            $comment->getId()
        );
    }
}
