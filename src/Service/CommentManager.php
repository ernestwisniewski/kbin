<?php declare(strict_types = 1);

namespace App\Service;

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

    public function __construct(CommentFactory $commentFactory)
    {
        $this->commentFactory = $commentFactory;
    }

    public function createComment(CommentDto $commentDto, User $user): Comment
    {
        return $this->commentFactory->createFromDto($commentDto, $user);
    }
}
