<?php declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
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
        $dto = new EntryCommentDto();
        $dto->setEntry($comment->getEntry());
        $dto->setBody($comment->getBody());

        return $dto;
    }
}
