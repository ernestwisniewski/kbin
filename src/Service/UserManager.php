<?php declare(strict_types=1);

namespace App\Service;

use App\Event\UserFollowedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\MagazineSubscribedEvent;
use App\Entity\Magazine;
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

    public function follow(User $follower, User $following)
    {
        $follower->follow($following);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }

    public function unfollow(User $follower, User $following)
    {
        $follower->unfollow($following);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }
}
