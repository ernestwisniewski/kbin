<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\CardanoPaymentInit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CardanoPaymentInit|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardanoPaymentInit|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardanoPaymentInit|null findOneByName(string $name)
 * @method CardanoPaymentInit[]    findAll()
 * @method CardanoPaymentInit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardanoPaymentInitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardanoPaymentInit::class);
    }

    public function findForRefresh(): array
    {
        return $this->findAll();
    }
}
