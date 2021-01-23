<?php declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\DTO\CommentDto;
use App\Entity\User;

class CommentFactory
{
    public function createFromDto(CommentDto $commentDto, User $user): Comment
    {
        return new Comment(
            $commentDto->getBody(),
            $commentDto->getEntry(),
            $user
        );
    }

    public function createDto(Comment $comment): CommentDto
    {
        $dto = new CommentDto();
        $dto->setEntry($comment->getEntry());
        $dto->setBody($comment->getBody());

        return $dto;
    }
}
