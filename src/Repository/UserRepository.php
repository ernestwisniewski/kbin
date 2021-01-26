<?php declare(strict_types = 1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\PagerfantaInterface;
use App\Entity\User;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    const PER_PAGE = 35;

    private EntryRepository $entryRepository;
    private EntryCommentRepository $entryCommentRepository;

    public function __construct(ManagerRegistry $registry, EntryRepository $entryRepository, EntryCommentRepository $entryCommentRepository)
    {
        parent::__construct($registry, User::class);
        $this->entryRepository        = $entryRepository;
        $this->entryCommentRepository = $entryCommentRepository;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findPublicActivity(int $page, User $user): PagerfantaInterface
    {
        $criteria = (new Criteria($page))
            ->setUser($user);

        $pagerfanta = $this->entryRepository->findByCriteria($criteria);

        try {
            $pagerfanta->setCurrentPage($criteria->getPage());
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }
}
