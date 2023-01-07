<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

class VoteRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function count(?\DateTimeImmutable $date = null): int
    {
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT id, 'entry' AS type FROM entry_vote {$this->where($date)}) 
        UNION 
        (SELECT id, 'entry_comment' AS type FROM entry_comment_vote {$this->where($date)})
        UNION 
        (SELECT id, 'post' AS type FROM post_vote {$this->where($date)})
        UNION 
        (SELECT id, 'post_comment' AS type FROM post_comment_vote {$this->where($date)})
        ";

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        return $stmt->rowCount();
    }

    private function where(?\DateTimeImmutable $date = null): string
    {
        return $date ? "WHERE created_at > '{$date->format('Y-m-d H:i:s')}'" : '';
    }
}
