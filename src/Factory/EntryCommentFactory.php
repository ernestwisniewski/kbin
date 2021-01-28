<?php declare(strict_types = 1);

namespace App\Factory;

use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\User;

class EntryCommentFactory
{
    public function createFromDto(EntryCommentDto $commentDto, User $user): EntryComment
    {
        return new EntryComment(
            $commentDto->getBody(),
            $commentDto->getEntry(),
            $user
        );
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return (new EntryCommentDto())->create(
            $comment->getBody(),
            $comment->getEntry(),
            $comment->getId()
        );
    }
}
