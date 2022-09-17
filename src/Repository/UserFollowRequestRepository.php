<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\AwardType;
use App\Entity\Settings;
use App\Entity\UserFollowRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Settings>
 *
 * @method UserFollowRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollowRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollowRequest[]    findAll()
 * @method UserFollowRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFollowRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AwardType::class);
    }
}
