<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\MagazineLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

/**
 * @method MagazineLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagazineLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagazineLog[]    findAll()
 * @method MagazineLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagazineLog::class);
    }

    public function listAll(int $page): PagerfantaInterface
    {
        {
            $qb = $this->createQueryBuilder('ml')
                ->orderBy('ml.id', 'DESC');

            $pager = new Pagerfanta(new QueryAdapter($qb));
            $pager->setMaxPerPage(25);
            $pager->setCurrentPage($page);

            return $pager;
        }
    }
}
