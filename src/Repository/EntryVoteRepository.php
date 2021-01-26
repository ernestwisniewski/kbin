<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\EntryVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryVote[]    findAll()
 * @method EntryVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryVote::class);
    }
}
