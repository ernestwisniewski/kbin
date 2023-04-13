<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MagazineSubscriptionRequest;
use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Settings>
 *
 * @method MagazineSubscriptionRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagazineSubscriptionRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagazineSubscriptionRequest[]    findAll()
 * @method MagazineSubscriptionRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineSubscriptionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagazineSubscriptionRequest::class);
    }
}
