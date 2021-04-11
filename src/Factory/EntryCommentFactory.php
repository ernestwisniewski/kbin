<?php declare(strict_types=1);

namespace App\Factory;

use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\User;

class EntryCommentFactory
{
    public function createFromDto(EntryCommentDto $dto, User $user): EntryComment
    {
        return new EntryComment(
            $dto->body,
            $dto->entry,
            $user,
            $dto->parent
        );
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return (new EntryCommentDto())->create(
            $comment->entry,
            $comment->body,
            null,
            $comment->getId()
        );
    }
}
