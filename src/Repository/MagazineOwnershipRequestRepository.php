<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MagazineOwnershipRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MagazineOwnershipRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagazineOwnershipRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagazineOwnershipRequest|null findOneByName(string $name)
 * @method MagazineOwnershipRequest[]    findAll()
 * @method MagazineOwnershipRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineOwnershipRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagazineOwnershipRequest::class);
    }
}
