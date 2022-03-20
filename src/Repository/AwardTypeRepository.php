<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\AwardType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AwardType|null find($id, $lockMode = null, $lockVersion = null)
 * @method AwardType|null findOneBy(array $criteria, array $orderBy = null)
 * @method AwardType|null findOneByName(string $name)
 * @method AwardType[]    findAll()
 * @method AwardType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardTypeRepository extends ServiceEntityRepository
{
    const PER_PAGE = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AwardType::class);
    }
}
