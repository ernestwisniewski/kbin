<?php declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EntryComment;
use App\DTO\CommentDto;
use App\Entity\User;

class EntryCommentFactory
{
    public function createFromDto(CommentDto $commentDto, User $user): EntryComment
    {
        return new EntryComment(
            $commentDto->getBody(),
            $commentDto->getEntry(),
            $user
        );
    }

    public function createDto(EntryComment $comment): CommentDto
    {
        $dto = new CommentDto();
        $dto->setEntry($comment->getEntry());
        $dto->setBody($comment->getBody());

        return $dto;
    }
}
