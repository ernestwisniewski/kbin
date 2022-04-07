<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReputationRepository extends ServiceEntityRepository
{
    const TYPE_ENTRY = 'threads';
    const TYPE_ENTRY_COMMENT = 'comments';
    const TYPE_POST = 'posts';
    const TYPE_POST_COMMENT = 'replies';

    const PER_PAGE = 31;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function getUserReputation(User $user, string $className, int $page = 1): PagerfantaInterface
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $table = $this->getEntityManager()->getClassMetadata($className)->getTableName().'_vote';

        $sql = "SELECT  date_trunc('day', v.created_at) as day, sum(v.choice) as points FROM ".$table." v 
                WHERE v.author_id = ".$user->getId()." GROUP BY 1";

        $stmt = $conn->prepare($sql);

        $stmt = $stmt->executeQuery();

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $stmt->fetchAllAssociative()
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function getUserReputationTotal(User $user): int
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $sql = "SELECT
                    (SELECT SUM(choice) FROM entry_vote WHERE author_id = :user) +
                    (SELECT SUM(choice) FROM entry_comment_vote WHERE author_id = :user) +
                    (SELECT SUM(choice) FROM post_vote WHERE author_id = :user) +
                    (SELECT SUM(choice) FROM post_comment_vote WHERE author_id = :user) as total";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('user', $user->getId());
        $stmt = $stmt->executeQuery();

        return $stmt->fetchAllAssociative()[0]['total'] ?? 0;
    }
}
