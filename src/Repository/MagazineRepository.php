<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Moderator;
use App\Entity\Report;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\Collections\SelectableAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Magazine|null find($id, $lockMode = null, $lockVersion = null)
 * @method Magazine|null findOneBy(array $criteria, array $orderBy = null)
 * @method Magazine|null findOneByName(string $name, array $orderBy = null)
 * @method Magazine[]    findAll()
 * @method Magazine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineRepository extends ServiceEntityRepository
{
    const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Magazine::class);
    }

    public function findAllPaginated(?int $page): PagerfantaInterface
    {
        $qb         = $this->createQueryBuilder('m')
            ->orderBy('m.subscriptionsCount', 'DESC');

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $qb
            )
        );

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
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

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findBlockedMagazines(int $page, User $user): PagerfantaInterface
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

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findModerators(Magazine $magazine, ?int $page = 1): PagerfantaInterface
    {
        $criteria = Criteria::create()->orderBy(['createdAt' => 'ASC']);

        $moderators = new Pagerfanta(new SelectableAdapter($magazine->moderators, $criteria));
        $moderators->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
        $moderators->setCurrentPage($page);

        return $moderators;
    }

    public function findModlog(Magazine $magazine, ?int $page = 1): PagerfantaInterface
    {
        $criteria = Criteria::create()->orderBy(['createdAt' => 'DESC']);

        $moderators = new Pagerfanta(new SelectableAdapter($magazine->logs, $criteria));
        $moderators->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
        $moderators->setCurrentPage($page);

        return $moderators;
    }

    public function findBans(Magazine $magazine, ?int $page = 1): PagerfantaInterface
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->gt('expiredAt', new DateTime()))
            ->orWhere(Criteria::expr()->isNull('expiredAt'))
            ->orderBy(['createdAt' => 'DESC']);

        $bans = new Pagerfanta(new SelectableAdapter($magazine->bans, $criteria));
        $bans->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
        $bans->setCurrentPage($page);

        return $bans;
    }

    public function findReports(Magazine $magazine, ?int $page = 1): PagerfantaInterface
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('status', Report::STATUS_PENDING))
            ->orderBy(['weight' => 'ASC']);

        $bans = new Pagerfanta(new SelectableAdapter($magazine->reports, $criteria));
        $bans->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
        $bans->setCurrentPage($page);

        return $bans;
    }

    public function findBadges(Magazine $magazine): Collection
    {
        return $magazine->badges;
    }

    public function findRandom(): ?Magazine
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m)');

        $totalRecords = $qb->getQuery()->getSingleScalarResult();

        if ($totalRecords < 1) {
            return null;
        }

        $rowToFetch = rand(0, $totalRecords - 1);

        return $qb
            ->select('m')
            ->setMaxResults(1)
            ->setFirstResult($rowToFetch)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findModeratedMagazines(User $user, ?int $page = 1): PagerfantaInterface
    {
        $dql =
            'SELECT m FROM '.Magazine::class.' m WHERE m IN ('.
            'SELECT IDENTITY(md.magazine) FROM '.Moderator::class.' md WHERE md.user = :user'.') ORDER BY m.lastActive DESC';

        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $user);

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $query
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }
}
