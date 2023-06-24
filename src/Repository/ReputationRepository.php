<?php

declare(strict_types=1);

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
    public const TYPE_ENTRY = 'threads';
    public const TYPE_ENTRY_COMMENT = 'comments';
    public const TYPE_POST = 'posts';
    public const TYPE_POST_COMMENT = 'replies';

    public const PER_PAGE = 48;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function getUserReputation(User $user, string $className, int $page = 1): PagerfantaInterface
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $table = $this->getEntityManager()->getClassMetadata($className)->getTableName().'_vote';

        $sql = "SELECT date_trunc('day', v.created_at) as day, sum(v.choice) as points FROM ".$table.' v 
                WHERE v.author_id = '.$user->getId().' GROUP BY day ORDER BY day DESC';

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

        $sql = 'SELECT
                    (SELECT SUM((up_votes * 2) - down_votes + favourite_count) FROM entry WHERE user_id = :user) +
                    (SELECT SUM((up_votes * 2) - down_votes + favourite_count) FROM entry_comment WHERE user_id = :user) +
                    (SELECT SUM((up_votes * 2) - down_votes + favourite_count) FROM post WHERE user_id = :user) +
                    (SELECT SUM((up_votes * 2) - down_votes + favourite_count) FROM post_comment WHERE user_id = :user) as total';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('user', $user->getId());
        $stmt = $stmt->executeQuery();

        return $stmt->fetchAllAssociative()[0]['total'] ?? 0;
    }
}
