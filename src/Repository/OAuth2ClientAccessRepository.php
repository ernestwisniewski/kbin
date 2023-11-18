<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OAuth2ClientAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuth2ClientAccess>
 *
 * @method OAuth2ClientAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method OAuth2ClientAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method OAuth2ClientAccess[]    findAll()
 * @method OAuth2ClientAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OAuth2ClientAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuth2ClientAccess::class);
    }

    public function save(OAuth2ClientAccess $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OAuth2ClientAccess $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getStats(
        string $intervalStr,
        ?\DateTime $start,
        ?\DateTime $end,
    ): array {
        $interval = $intervalStr ?? 'hour';
        switch ($interval) {
            case 'all':
                return $this->aggregateTotalStats();
            case 'year':
            case 'month':
            case 'day':
            case 'hour':
            case 'minute':
            case 'second':
            case 'milliseconds':
                break;
            default:
                throw new \LogicException('Invalid interval provided');
        }

        return $this->aggregateStats($interval, $start, $end);
    }

    // Todo - stats need improvement for sure but that's out of the scope of making the starting API
    private function aggregateStats(string $interval, ?\DateTime $start, ?\DateTime $end): array
    {
        if (null === $end) {
            $end = new \DateTime();
        }

        if (null === $start) {
            $start = new \DateTime('-1 '.$interval);
        }

        if ($end < $start) {
            throw new \LogicException('End date must be after start date!');
        }

        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT c.name as client, date_trunc(?, e.created_at) as datetime, COUNT(e) as count FROM oauth2_client_access e
                    JOIN oauth2_client c on c.identifier = e.client_id
                    WHERE e.created_at BETWEEN ? AND ?
                    GROUP BY 1, 2 ORDER BY 3 DESC';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $interval);
        $stmt->bindValue(2, $start, 'datetime');
        $stmt->bindValue(3, $end, 'datetime');

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    private function aggregateTotalStats(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT e.client_id as client, COUNT(e) as count FROM oauth2_client_access e 
                    GROUP BY 1 ORDER BY 2 DESC';

        $stmt = $conn->prepare($sql);

        return $stmt->executeQuery()->fetchAllAssociative();
    }
}
