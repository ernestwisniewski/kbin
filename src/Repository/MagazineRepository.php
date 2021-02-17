<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Magazine;
use Pagerfanta\Doctrine\Collections\SelectableAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Magazine|null find($id, $lockMode = null, $lockVersion = null)
 * @method Magazine|null findOneBy(array $criteria, array $orderBy = null)
 * @method Magazine[]    findAll()
 * @method Magazine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineRepository extends ServiceEntityRepository
{
    const PER_PAGE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Magazine::class);
    }

    public function findAllPaginated(?int $page): PagerfantaInterface
    {
        $qb = $this->createQueryBuilder('m');

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $qb
            )
        );

        $pagerfanta->setMaxPerPage(self::PER_PAGE);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findSubscribedMagazines(int $page, User $user): PagerfantaInterface
    {
        $dql =
            'SELECT m FROM '.Magazine::class.' m WHERE m IN ('.
            'SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :user'.')';

        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $user);

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $query
            )
        );

        $pagerfanta->setMaxPerPage(self::PER_PAGE);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findBlockedMagazines(int $page, User $user)
    {
        $dql =
            'SELECT m FROM '.Magazine::class.' m WHERE m IN ('.
            'SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :user'.')';

        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $user);

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $query
            )
        );

        $pagerfanta->setMaxPerPage(self::PER_PAGE);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findModeratorsPaginated(Magazine $magazine, ?int $page = 1)
    {
        $criteria = Criteria::create()->orderBy(['createdAt' => 'ASC']);

        $moderators = new Pagerfanta(new SelectableAdapter($magazine->getModerators(), $criteria));
        $moderators->setMaxPerPage(self::PER_PAGE);
        $moderators->setCurrentPage($page);

        return $moderators;
    }

    public function getBansPaginated(Magazine $magazine, ?int $page = 1)
    {
        $criteria = Criteria::create()->orderBy(['createdAt' => 'DESC']);

        $bans = new Pagerfanta(new SelectableAdapter($magazine->getBans(), $criteria));
        $bans->setMaxPerPage(self::PER_PAGE);
        $bans->setCurrentPage($page);

        return $bans;
    }
}
