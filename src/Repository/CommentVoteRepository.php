<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\CommentVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommentVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentVote[]    findAll()
 * @method CommentVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentVote::class);
    }

    // /**
    //  * @return CommentVote[] Returns an array of CommentVote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CommentVote
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
