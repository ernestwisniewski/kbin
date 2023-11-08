<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ModeratorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**`
 * @method ModeratorRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModeratorRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModeratorRequest|null findOneByName(string $name)
 * @method ModeratorRequest[]    findAll()
 * @method ModeratorRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModeratorRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModeratorRequest::class);
    }
}
