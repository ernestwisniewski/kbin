<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\UserDto;
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
        $this->entityManager   = $entityManager;
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

    public function edit(User $user, UserDto $dto): User
    {
        return $user;
    }

    public function createDto(User $user): UserDto
    {
        $dto = new UserDto();

        $dto->setUsername($user->getUsername());
        $dto->setEmail($user->getEmail());

        return $dto;
    }
}
