<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\UserCreatedMessage;
use App\Message\UserUpdatedMessage;
use App\Event\UserFollowedEvent;
use App\Security\EmailVerifier;
use App\Event\UserBlockEvent;
use App\DTO\RegisterUserDto;
use App\DTO\UserDto;
use App\Entity\User;

class UserManager
{
    public function __construct(
        private UserPasswordEncoderInterface $passwordEncoder,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EmailVerifier $emailVerifier,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function follow(User $follower, User $following)
    {
        $follower->unblock($following);

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

    /**
     * @IsGranted("ROLE_USER")
     */
    public function block(User $blocker, User $blocked)
    {
        $this->unfollow($blocker, $blocked);

        $blocker->block($blocked);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function unblock(User $blocker, User $blocked)
    {
        $blocker->unblock($blocked);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserFollowedEvent($blocker, $blocked));
    }

    public function create(RegisterUserDto $dto): User
    {
        $user = new User($dto->email, $dto->username, '');

        $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new UserCreatedMessage($user->getId()));

        return $user;
    }

    public function edit(User $user, UserDto $dto): User
    {
        if ($dto->avatar) {
            $user->setAvatar($dto->avatar);
        }

        if ($dto->plainPassword) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->plainPassword));
        }

        if ($dto->email !== $user->email) {
            $user->isVerified = false;
            $user->email      = $dto->email;

            $this->entityManager->flush();

            $this->messageBus->dispatch(new UserUpdatedMessage($user->getId()));
        }

        $this->entityManager->flush();

        return $user;
    }

    public function createDto(User $user): UserDto
    {
        $dto = new UserDto();

        $dto->setId($user->getId());
        $dto->username = $user->getUsername();
        $dto->email    = $user->email;

        return $dto;
    }

    public function verify(Request $request, User $user): void
    {
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }

    public function toggleTheme(User $user): void
    {
        $user->toggleTheme();

        $this->entityManager->flush();
    }
}
