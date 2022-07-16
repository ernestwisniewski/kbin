<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApInbox;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApInbox|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApInbox|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApInbox|null findOneByName(string $name)
 * @method ApInbox[]    findAll()
 * @method ApInbox[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApInboxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApInbox::class);
    }
}
