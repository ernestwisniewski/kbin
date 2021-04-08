<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\PostCommentDto;
use App\Entity\PostComment;
use App\Entity\User;

class PostCommentFactory
{
    public function createFromDto(PostCommentDto $commentDto, User $user): PostComment
    {
        return new PostComment(
            $commentDto->body,
            $commentDto->post,
            $user,
            $commentDto->parent
        );
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return (new PostCommentDto())->create(
            $comment->post,
            $comment->body,
            null,
            $comment->getId()
        );
    }
}
