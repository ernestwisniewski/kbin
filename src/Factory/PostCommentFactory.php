<?php declare(strict_types = 1);

namespace App\Factory;

use App\DTO\PostCommentDto;
use App\Entity\PostComment;
use App\Entity\User;

class PostCommentFactory
{
    public function createFromDto(PostCommentDto $dto, User $user): PostComment
    {
        return new PostComment(
            $dto->body,
            $dto->post,
            $user,
            $dto->parent,
            $dto->ip
        );
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return (new PostCommentDto())->create(
            $comment->post,
            $comment->body,
            $comment->user,
            null,
            $comment->countUpVotes(),
            $comment->createdAt,
            $comment->lastActive,
            $comment->getId()
        );
    }
}
