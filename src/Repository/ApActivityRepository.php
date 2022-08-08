<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApActivity;
use App\Entity\Entry;
use App\Entity\Post;
use App\Entity\PostComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApActivity|null findOneByName(string $name)
 * @method ApActivity[]    findAll()
 * @method ApActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApActivity::class);
    }

    public function findByObjectId(string $apId): ?array
    {
        $entryClass        = Entry::class;
        $entryCommentClass = Entry::class;
        $postClass         = Post::class;
        $postCommentClass  = PostComment::class;

        $conn              = $this->_em->getConnection();
        $sql               = "
        (SELECT id, '{$entryClass}' AS type FROM entry 
        WHERE ap_id = '{$apId}') 
        UNION 
        (SELECT id, '{$entryCommentClass}' AS type FROM entry_comment
        WHERE ap_id = '{$apId}')
        UNION 
        (SELECT id, '{$postClass}' AS type FROM post
        WHERE ap_id = '{$apId}')
        UNION 
        (SELECT id, '{$postCommentClass}' AS type FROM post_comment 
        WHERE ap_id = '{$apId}')
        ";

        $stmt = $conn->prepare($sql);

        $results = $stmt->executeQuery()->fetchAllAssociative();

        return count($results) ? $results[0] : null;
    }
}
