<?php declare(strict_types = 1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Factory\CommentFactory;
use App\DTO\CommentDto;
use App\Entity\Comment;
use App\Entity\User;

class CommentManager
{
    /**
     * @var CommentFactory
     */
    private $commentFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(CommentFactory $commentFactory, EntityManagerInterface $entityManager)
    {
        $this->commentFactory = $commentFactory;
        $this->entityManager = $entityManager;
    }

    public function createComment(CommentDto $commentDto, User $user): Comment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $this->entityManager->persist($comment);

        return $comment;
    }
}
