<?php declare(strict_types = 1);

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
        $dto             = new EntryCommentDto();
        $dto->entry      = $comment->entry;
        $dto->user       = $comment->user;
        $dto->body       = $comment->body;
        $dto->image      = $comment->image;
        $dto->uv         = $comment->countUpVotes();
        $dto->dv         = $comment->countDownVotes();
        $dto->createdAt  = $comment->createdAt;
        $dto->lastActive = $comment->lastActive;
        $dto->setId($comment->getId());

        return $dto;
    }
}
