<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\PostVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostVote[]    findAll()
 * @method PostVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostVote::class);
    }
}
