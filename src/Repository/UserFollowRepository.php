<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Settings;
use App\Entity\UserFollow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Settings>
 *
 * @method UserFollow|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollow|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollow[]    findAll()
 * @method UserFollow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFollowRepository
{
}
