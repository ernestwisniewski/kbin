<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\DomainSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DomainSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method DomainSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method DomainSubscription[]    findAll()
 * @method DomainSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainSubscription::class);
    }
}
