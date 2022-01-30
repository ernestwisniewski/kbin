<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function get_class;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User|null findOneByUsername(string $value)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{
    const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername(string $username)
    {
        return $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier($val)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :email')
            ->orWhere('u.email = :email')
            ->setParameter('email', $val)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findPublicActivity(int $page, User $user): PagerfantaInterface
    {
        // @todo union adapter
        $conn = $this->_em->getConnection();
        $sql  = "
        (SELECT id, created_at, 'entry' AS type FROM entry WHERE user_id = {$user->getId()}) 
        UNION 
        (SELECT id, created_at, 'entry_comment' AS type FROM entry_comment WHERE user_id = {$user->getId()})
        UNION 
        (SELECT id, created_at, 'post' AS type FROM post WHERE user_id = {$user->getId()})
        UNION 
        (SELECT id, created_at, 'post_comment' AS type FROM post_comment WHERE user_id = {$user->getId()})
        ORDER BY created_at DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $stmt->fetchAllAssociative()
            )
        );

        $countAll = $pagerfanta->count();

        try {
            $pagerfanta->setMaxPerPage(20000);
            $pagerfanta->setCurrentPage(1);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $result = $pagerfanta->getCurrentPageResults();

        $entries = $this->_em->getRepository(Entry::class)->findBy(['id' => $this->getOverviewIds((array) $result, 'entry')]);
        $this->_em->getRepository(Entry::class)->hydrate(...$entries);
        $entryComments = $this->_em->getRepository(EntryComment::class)->findBy(['id' => $this->getOverviewIds((array) $result, 'entry_comment')]);
        $this->_em->getRepository(EntryComment::class)->hydrate(...$entryComments);
        $post = $this->_em->getRepository(Post::class)->findBy(['id' => $this->getOverviewIds((array) $result, 'post')]);
        $this->_em->getRepository(Post::class)->hydrate(...$post);
        $postComment = $this->_em->getRepository(PostComment::class)->findBy(['id' => $this->getOverviewIds((array) $result, 'post_comment')]);
        $this->_em->getRepository(PostComment::class)->hydrate(...$postComment);

        $result = array_merge($entries, $entryComments, $post, $postComment);
        uasort($result, fn($a, $b) => $a->getCreatedAt() > $b->getCreatedAt() ? -1 : 1);

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $result
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
            $pagerfanta->setMaxNbPages($countAll > 0 ? ((int) ceil(($countAll / self::PER_PAGE))) : 1);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    private function getOverviewIds(array $result, string $type): array
    {
        $result = array_filter($result, fn($subject) => $subject['type'] === $type);

        return array_map(fn($subject) => $subject['id'], $result);
    }

    public function findFollowedUsers(int $page, User $user): PagerfantaInterface
    {
        $dql =
            'SELECT u FROM '.User::class.' u WHERE u IN ('.
            'SELECT IDENTITY(us.following) FROM '.UserFollow::class.' us WHERE us.follower = :user'.')';

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


    public function findFollowUsers(int $page, User $user): PagerfantaInterface
    {
        $dql =
            'SELECT u FROM '.User::class.' u WHERE u IN ('.
            'SELECT IDENTITY(us.follower) FROM '.UserFollow::class.' us WHERE us.follower = :user'.')';

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

    public function findBlockedUsers(int $page, User $user): PagerfantaInterface
    {
        $dql =
            'SELECT u FROM '.User::class.' u WHERE u IN ('.
            'SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :user'.')';

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
