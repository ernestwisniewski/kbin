<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\EntryCommentVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryCommentVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryCommentVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryCommentVote[]    findAll()
 * @method EntryCommentVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCommentVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryCommentVote::class);
    }

}
