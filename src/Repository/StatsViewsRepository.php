<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\User;
use Doctrine\DBAL\ParameterType;

class StatsViewsRepository extends StatsRepository
{
    public function getOverallStats(
        User $user = null,
        Magazine $magazine = null,
        bool $onlyLocal = null
    ): array {
        $this->user = $user;
        $this->magazine = $magazine;
        $this->onlyLocal = $onlyLocal;

        return $this->sort($this->getMonthlyStats());
    }

    private function getMonthlyStats(): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $onlyLocalWhere = $this->onlyLocal ? ' AND e.ap_id IS NULL' : '';
        if ($this->user) {
            $sql = 'SELECT to_char(e.created_at,\'Mon\') as month, extract(year from e.created_at) as year, SUM(e.views) as count FROM entry e
                    WHERE e.user_id = :userId '.$onlyLocalWhere.' GROUP BY 1,2';
        } elseif ($this->magazine) {
            $sql = 'SELECT to_char(e.created_at,\'Mon\') as month, extract(year from e.created_at) as year, SUM(e.views) as count FROM entry e
                    WHERE e.magazine_id = :magazineId '.$onlyLocalWhere.' GROUP BY 1,2';
        } else {
            if (!$this->onlyLocal) {
                $sql = 'SELECT to_char(e.created_at,\'Mon\') as month, extract(year from e.created_at) as year, SUM(e.views) as count
                    FROM entry e GROUP BY 1,2';
            } else {
                $sql = 'SELECT to_char(e.created_at,\'Mon\') as month, extract(year from e.created_at) as year, SUM(e.views) as count
                    FROM entry e WHERE e.ap_id IS NULL GROUP BY 1,2';
            }
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

    public function getStatsByTime(\DateTime $start, ?User $user, ?Magazine $magazine, ?bool $onlyLocal): array
    {
        $this->start = $start;
        $this->user = $user;
        $this->magazine = $magazine;
        $this->onlyLocal = $onlyLocal;

        return $this->prepareContentDaily($this->getDailyStats());
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
                return $this->aggregateTotalStats($magazine);
            case 'year':
            case 'month':
            case 'day':
            case 'hour':
                break;
            default:
                throw new \LogicException('Invalid interval provided');
        }

        $this->start = $start ?? new \DateTime('-1 '.$interval);

        return $this->aggregateStats($magazine, $interval, $end);
    }

    private function aggregateStats(?Magazine $magazine, string $interval, ?\DateTime $end): array
    {
        if (null === $end) {
            $end = new \DateTime();
        }

        if ($end < $this->start) {
            throw new \LogicException('End date must be after start date!');
        }

        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT date_trunc(?, e.created_at) as datetime, SUM(e.views) as count FROM entry e 
                    WHERE e.created_at BETWEEN ? AND ?';
        if ($magazine) {
            $sql .= ' AND e.magazine_id = ?';
        }
        if ($this->onlyLocal) {
            $sql .= ' AND e.ap_id IS NULL';
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

    private function aggregateTotalStats(?Magazine $magazine): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT SUM(e.views) as count FROM entry e';
        if ($magazine) {
            $sql .= ' WHERE e.magazine_id = :magazineId';
        }
        if ($this->onlyLocal) {
            $sql = $sql.' AND e.ap_id IS NULL';
        }

        $stmt = $conn->prepare($sql);
        if ($magazine) {
            $stmt->bindValue('magazineId', $magazine->getId());
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    private function getDailyStats(): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $onlyLocalWhere = $this->onlyLocal ? 'AND e.ap_id IS NULL' : '';
        if ($this->user) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e 
                    WHERE e.created_at >= '".$this->start->format('Y-m-d H:i:s')."' 
                    AND e.user_id = ".$this->user->getId().'
                    '.$onlyLocalWhere.'
                    GROUP BY 1';
        } elseif ($this->magazine) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e 
                    WHERE e.created_at >= '".$this->start->format('Y-m-d H:i:s')."' 
                    AND e.magazine_id = ".$this->magazine->getId().' 
                    '.$onlyLocalWhere.'
                    GROUP BY 1';
        } else {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e 
                    WHERE e.created_at >= '".$this->start->format('Y-m-d H:i:s')."' 
                    ".$onlyLocalWhere.'
                    GROUP BY 1';
        }

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        $results = $stmt->fetchAllAssociative();

        usort($results, fn ($a, $b): int => $a['day'] <=> $b['day']);

        return $results;
    }
}
