<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserNote[]    findAll()
 * @method UserNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNote::class);
    }
}
