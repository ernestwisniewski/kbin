<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

class VoteRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function count(): int
    {
        $conn = $this->entityManager->getConnection();
        $sql  = "
        (SELECT id, 'entry' AS type FROM entry_vote) 
        UNION 
        (SELECT id, 'entry_comment' AS type FROM entry_comment_vote)
        UNION 
        (SELECT id, 'post' AS type FROM post_vote)
        UNION 
        (SELECT id, 'post_comment' AS type FROM post_comment_vote)
        ";
        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        return $stmt->rowCount();
    }
}
