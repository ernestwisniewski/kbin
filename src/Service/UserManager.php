<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDto;
use App\DTO\UserDto;
use App\Entity\User;
use App\Event\User\UserBlockEvent;
use App\Event\User\UserFollowedEvent;
use App\Factory\UserFactory;
use App\Message\UserCreatedMessage;
use App\Message\UserUpdatedMessage;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    public function __construct(
        private UserFactory $factory,
        private UserPasswordEncoderInterface $encoder,
        private EventDispatcherInterface $dispatcher,
        private MessageBusInterface $bus,
        private EmailVerifier $verifier,
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

        $this->dispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function block(User $blocker, User $blocked)
    {
        $this->unfollow($blocker, $blocked);

        $blocker->block($blocked);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function unfollow(User $follower, User $following)
    {
        $follower->unfollow($following);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function unblock(User $blocker, User $blocked)
    {
        $blocker->unblock($blocked);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserFollowedEvent($blocker, $blocked));
    }

    public function create(RegisterUserDto $dto, bool $verifyUserEmail = true): User
    {
        $user = new User($dto->email, $dto->username, '');

        $user->setPassword($this->encoder->encodePassword($user, $dto->plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($verifyUserEmail) {
            $this->bus->dispatch(new UserCreatedMessage($user->getId()));
        }

        return $user;
    }

    public function edit(User $user, UserDto $dto): User
    {
        if ($dto->avatar) {
            $user->setAvatar($dto->avatar);
        }

        if ($dto->plainPassword) {
            $user->setPassword($this->encoder->encodePassword($user, $dto->plainPassword));
        }

        if ($dto->email !== $user->email) {
            $user->isVerified = false;
            $user->email      = $dto->email;

            $this->entityManager->flush();

            $this->bus->dispatch(new UserUpdatedMessage($user->getId()));
        }

        $this->entityManager->flush();

        return $user;
    }

    public function createDto(User $user): UserDto
    {
        return $this->factory->createDto($user);
    }

    public function verify(Request $request, User $user): void
    {
        $this->verifier->handleEmailConfirmation($request, $user);
    }

    public function toggleTheme(User $user): void
    {
        $user->toggleTheme();

        $this->entityManager->flush();
    }
}
