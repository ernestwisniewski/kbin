<?php declare(strict_types = 1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\DTO\CommentDto;
use App\Entity\User;

class CommentFactory
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createFromDto(CommentDto $commentDto, User $user): Comment
    {
        $comment = new Comment(
            $commentDto->getBody(),
            $commentDto->getEntry(),
            $user
        );

        $this->entityManager->persist($comment);

        return $comment;
    }
}
