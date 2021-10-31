<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\MagazineBan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MagazineBan|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagazineBan|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagazineBan[]    findAll()
 * @method MagazineBan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineBanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagazineBan::class);
    }
}
