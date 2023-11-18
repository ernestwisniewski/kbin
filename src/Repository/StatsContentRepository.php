<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Doctrine\DBAL\ParameterType;
use JetBrains\PhpStorm\ArrayShape;

class StatsContentRepository extends StatsRepository
{
    #[ArrayShape(['entries' => 'array', 'comments' => 'array', 'posts' => 'array', 'replies' => 'array'])]
    public function getOverallStats(
        User $user = null,
        Magazine $magazine = null,
        bool $onlyLocal = null
    ): array {
        $this->user = $user;
        $this->magazine = $magazine;
        $this->onlyLocal = $onlyLocal;

        $entries = $this->getMonthlyStats('entry');
        $comments = $this->getMonthlyStats('entry_comment');
        $posts = $this->getMonthlyStats('post');
        $replies = $this->getMonthlyStats('post_comment');

        $startDate = $this->sort(
            array_merge(
                $this->getStartDate($entries),
                $this->getStartDate($comments),
                $this->getStartDate($posts),
                $this->getStartDate($replies)
            )
        );

        if (empty($startDate)) {
            return [
                'entries' => [],
                'comments' => [],
                'posts' => [],
                'replies' => [],
            ];
        }

        return [
            'entries' => $this->prepareContentOverall(
                $this->sort($entries),
                $startDate[0]['year'],
                $startDate[0]['month']
            ),
            'comments' => $this->prepareContentOverall(
                $this->sort($comments),
                $startDate[0]['year'],
                $startDate[0]['month']
            ),
            'posts' => $this->prepareContentOverall($this->sort($posts), $startDate[0]['year'], $startDate[0]['month']),
            'replies' => $this->prepareContentOverall(
                $this->sort($replies),
                $startDate[0]['year'],
                $startDate[0]['month']
            ),
        ];
    }

    private function getMonthlyStats(string $table): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($this->user) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count 
                    FROM ".$table.' e WHERE e.user_id = :userId GROUP BY 1,2';
        } elseif ($this->magazine) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count 
                    FROM ".$table.' e WHERE e.magazine_id = :magazineId GROUP BY 1,2';
        } else {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count 
                    FROM ".$table.' e GROUP BY 1,2';
        }

        $stmt = $conn->prepare($sql);
        if ($this->user) {
            $stmt->bindValue('userId', $this->user->getId());
        } elseif ($this->magazine) {
            $stmt->bindValue('magazineId', $this->magazine->getId());
        }
        $stmt = $stmt->executeQuery();

        return array_map(fn ($val) => [
            'month' => date_parse($val['month'])['month'],
            'year' => (int) $val['year'],
            'count' => (int) $val['count'],
        ], $stmt->fetchAllAssociative());
    }

    #[ArrayShape(['entries' => 'array', 'comments' => 'array', 'posts' => 'array', 'replies' => 'array'])]
    public function getStatsByTime(\DateTime $start, User $user = null, Magazine $magazine = null, bool $onlyLocal = null): array
    {
        $this->start = $start;
        $this->user = $user;
        $this->magazine = $magazine;
        $this->onlyLocal = $onlyLocal;

        return [
            'entries' => $this->prepareContentDaily($this->getDailyStats('entry')),
            'comments' => $this->prepareContentDaily($this->getDailyStats('entry_comment')),
            'posts' => $this->prepareContentDaily($this->getDailyStats('post')),
            'replies' => $this->prepareContentDaily($this->getDailyStats('post_comment')),
        ];
    }

    private function getDailyStats(string $table): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $onlyLocalWhere = $this->onlyLocal ? ' AND e.ap_id IS NULL' : '';
        if ($this->user) {
            $sql = "SELECT date_trunc('day', e.created_at) as day, COUNT(e.id) as count FROM ".$table.' e
                    WHERE e.created_at >= :startDate AND e.user_id = :userId '.$onlyLocalWhere.' GROUP BY 1';
        } elseif ($this->magazine) {
            $sql = "SELECT date_trunc('day', e.created_at) as day, COUNT(e.id) as count FROM ".$table.' e
                    WHERE e.created_at >= :startDate AND e.magazine_id = :magazineId '.$onlyLocalWhere.' GROUP BY 1';
        } else {
            $sql = "SELECT date_trunc('day', e.created_at) as day, COUNT(e.id) as count FROM ".$table.' e
                    WHERE e.created_at >= :startDate '.$onlyLocalWhere.' GROUP BY 1';
        }

        $stmt = $conn->prepare($sql);
        if ($this->user) {
            $stmt->bindValue('userId', $this->user->getId());
        } elseif ($this->magazine) {
            $stmt->bindValue('magazineId', $this->magazine->getId());
        }
        $stmt->bindValue('startDate', $this->start->format('Y-m-d H:i:s'));
        $stmt = $stmt->executeQuery();

        $results = $stmt->fetchAllAssociative();

        usort($results, fn ($a, $b): int => $a['day'] <=> $b['day']);

        return $results;
    }

    public function getStats(
        ?Magazine $magazine,
        string $intervalStr,
        ?\DateTime $start,
        ?\DateTime $end,
        ?bool $onlyLocal
    ): array {
        $this->onlyLocal = $onlyLocal;
        $interval = $intervalStr ?? 'month';
        switch ($interval) {
            case 'all':
                $toReturn = [
                    'entry' => $this->aggregateTotalStats('entry', $magazine),
                    'entry_comment' => $this->aggregateTotalStats('entry_comment', $magazine),
                    'post' => $this->aggregateTotalStats('post', $magazine),
                    'post_comment' => $this->aggregateTotalStats('post_comment', $magazine),
                ];

                return $toReturn;
            case 'year':
            case 'month':
            case 'day':
            case 'hour':
                break;
            default:
                throw new \LogicException('Invalid interval provided');
        }

        $this->start = $start ?? new \DateTime('-1 '.$interval);

        $toReturn = [
            'entry' => $this->aggregateStats('entry', $magazine, $interval, $end),
            'entry_comment' => $this->aggregateStats('entry_comment', $magazine, $interval, $end),
            'post' => $this->aggregateStats('post', $magazine, $interval, $end),
            'post_comment' => $this->aggregateStats('post_comment', $magazine, $interval, $end),
        ];

        return $toReturn;
    }

    private function aggregateStats(string $table, ?Magazine $magazine, string $interval, ?\DateTime $end): array
    {
        if (null === $end) {
            $end = new \DateTime();
        }

        if ($end < $this->start) {
            throw new \LogicException('End date must be after start date!');
        }

        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT date_trunc(?, e.created_at) as datetime, count(e.id) as count FROM '.$table.' e 
                    WHERE e.created_at BETWEEN ? AND ?';
        if ($magazine) {
            $sql .= ' AND e.magazine_id = ?';
        }
        if ($this->onlyLocal) {
            $sql = $sql.' AND e.ap_id IS NULL';
        }
        $sql = $sql.' GROUP BY 1 ORDER BY 1';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $interval);
        $stmt->bindValue(2, $this->start, 'datetime');
        $stmt->bindValue(3, $end, 'datetime');
        if ($magazine) {
            $stmt->bindValue(4, $magazine->getId(), ParameterType::INTEGER);
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    private function aggregateTotalStats(string $table, ?Magazine $magazine): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(e.id) as count FROM '.$table.' e';
        if ($magazine) {
            $sql .= ' WHERE e.magazine_id = ?';
        }
        if ($this->onlyLocal) {
            $sql = $sql.' AND e.ap_id IS NULL';
        }

        $stmt = $conn->prepare($sql);
        if ($magazine) {
            $stmt->bindValue(1, $magazine->getId());
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function countLocalPosts(): int
    {
        $entries = $this->_em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(Entry::class, 'e')
            ->where('e.apId IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $posts = $this->_em->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Post::class, 'p')
            ->where('p.apId IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $entries + $posts;
    }

    public function countLocalComments(): int
    {
        $entryComments = $this->_em->createQueryBuilder()
            ->select('COUNT(ec.id)')
            ->from(EntryComment::class, 'ec')
            ->where('ec.apId IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $postComments = $this->_em->createQueryBuilder()
            ->select('COUNT(pc.id)')
            ->from(PostComment::class, 'pc')
            ->where('pc.apId IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $entryComments + $postComments;
    }

    public function countUsers(\DateTime $startDate = null): int
    {
        $users = $this->_em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(User::class, 'u')
            ->where('u.apId IS NULL');

        if ($startDate) {
            $users->andWhere('u.lastActive >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        return $users->getQuery()
            ->getSingleScalarResult();
    }
}
