<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\PostCommentVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostCommentVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostCommentVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostCommentVote[]    findAll()
 * @method PostCommentVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCommentVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostCommentVote::class);
    }
}
