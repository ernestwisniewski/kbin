<?php declare(strict_types=1);

namespace App\Service;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\UserFollowedEvent;
use App\Entity\User;

class UserManager
{
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;

    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function follow(User $follower, User $following)
    {
        $follower->follow($following);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function unfollow(User $follower, User $following)
    {
        $follower->unfollow($following);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }
}
