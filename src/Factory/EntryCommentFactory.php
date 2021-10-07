<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\EntryCommentDto;
use App\Entity\EntryComment;
use App\Entity\User;

class EntryCommentFactory
{
    public function createFromDto(EntryCommentDto $dto, User $user): EntryComment
    {
        return new EntryComment(
            $dto->body,
            $dto->entry,
            $user,
            $dto->parent,
            $dto->ip
        );
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return (new EntryCommentDto())->create(
            $comment->entry,
            $comment->body,
            $comment->user,
            $comment->image,
            $comment->countUpVotes(),
            $comment->countDownVotes(),
            $comment->createdAt,
            $comment->lastActive,
            $comment->getId()
        );
    }
}
