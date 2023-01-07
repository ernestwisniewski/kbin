<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Embed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Embed|null find($id, $lockMode = null, $lockVersion = null)
 * @method Embed|null findOneBy(array $criteria, array $orderBy = null)
 * @method Embed|null findOneByName(string $name)
 * @method Embed[]    findAll()
 * @method Embed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmbedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Embed::class);
    }

    public function add(Embed $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Embed $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
