<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
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

        $table = $this->getEntityManager()->getClassMetadata($className)->getTableName();

        $sql = "(SELECT date_trunc('day', subquery.created_at) as day,
                SUM(CASE WHEN subquery.choice = 1 THEN subquery.count_id * 2 ELSE subquery.count_id END) as points
                FROM (
                    SELECT date_trunc('day', v.created_at) as created_at, v.id, COUNT(v.id) as count_id, v.choice
                    FROM {$table}_vote v
                    WHERE v.author_id = :userId AND v.user_id != :userId
                    GROUP BY created_at, v.id, v.choice
                ) as subquery
                GROUP BY day
                ORDER BY day DESC)    
                UNION ALL   
                (SELECT date_trunc('day', f.created_at) as day, count(f.id)
                FROM favourite f
                LEFT JOIN {$table} fj ON f.{$table}_id = fj.id
                WHERE fj.user_id = :userId AND f.user_id != :userId
                GROUP BY day, f.entry_id
                ORDER BY day DESC)
                ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $user->getId());
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
        return $this->getUserReputationVotesCount($user) + $this->getUserReputationFavouritesCount($user);
    }

    private function getUserReputationVotesCount(User $user): int
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $sql = "SELECT
                    ({$this->getUserReputationVotesSubquery(Entry::class)}) +
                    ({$this->getUserReputationVotesSubquery(EntryComment::class)}) +
                    ({$this->getUserReputationVotesSubquery(Post::class)}) +
                    ({$this->getUserReputationVotesSubquery(PostComment::class)}) as total";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('user', $user->getId());
        $stmt = $stmt->executeQuery();

        return (int) $stmt->fetchAllAssociative()[0]['total'] ?? 0;
    }

    private function getUserReputationVotesSubquery(string $className): string
    {
        $type = $this->getEntityManager()->getClassMetadata($className)->getTableName();

        return "SELECT SUM(
            (SELECT COUNT(id) FROM {$type}_vote WHERE author_id = :user AND user_id != :user AND choice = 1) * 2 -
            (SELECT COUNT(id) FROM {$type}_vote WHERE author_id = :user AND user_id != :user AND choice = -1)
        )";
    }

    private function getUserReputationFavouritesCount(User $user): int
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $sql = "SELECT
                    ({$this->getUserReputationFavouritesSubquery(Entry::class)}) +
                    ({$this->getUserReputationFavouritesSubquery(EntryComment::class)}) +
                    ({$this->getUserReputationFavouritesSubquery(Post::class)}) +
                    ({$this->getUserReputationFavouritesSubquery(PostComment::class)}) as total";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('user', $user->getId());
        $stmt = $stmt->executeQuery();

        return (int) $stmt->fetchAllAssociative()[0]['total'] ?? 0;
    }

    private function getUserReputationFavouritesSubquery(string $className): string
    {
        $type = $this->getEntityManager()->getClassMetadata($className)->getTableName();

        return "SELECT count(f.id)
                FROM favourite f
                LEFT JOIN {$type} fj ON f.{$type}_id = fj.id
                WHERE fj.user_id = :user AND f.user_id != :user";
    }
}
