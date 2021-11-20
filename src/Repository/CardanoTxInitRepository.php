<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\CardanoTxInit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CardanoTxInit|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardanoTxInit|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardanoTxInit|null findOneByName(string $name)
 * @method CardanoTxInit[]    findAll()
 * @method CardanoTxInit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardanoTxInitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardanoTxInit::class);
    }

    public function findForRefresh(): array
    {
        return $this->findAll();
    }
}
