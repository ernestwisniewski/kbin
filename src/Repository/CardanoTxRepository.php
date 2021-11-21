<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\CardanoTx;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CardanoTx|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardanoTx|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardanoTx|null findOneByName(string $name)
 * @method CardanoTx[]    findAll()
 * @method CardanoTx[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardanoTxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardanoTx::class);
    }

    public function findForRefresh(): array
    {
        return $this->findAll();
    }
}
