<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\CardanoWalletAddressDto;
use App\DTO\UserDto;
use App\Entity\User;
use App\Event\User\UserBlockEvent;
use App\Event\User\UserFollowedEvent;
use App\Factory\UserFactory;
use App\Message\DeleteUserMessage;
use App\Message\UserCreatedMessage;
use App\Message\UserUpdatedMessage;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserManager
{
    public function __construct(
        private UserFactory $factory,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private EventDispatcherInterface $dispatcher,
        private MessageBusInterface $bus,
        private EmailVerifier $verifier,
        private EntityManagerInterface $entityManager,
        private RateLimiterFactory $userRegisterLimiter,
    ) {
    }

    public function follow(User $follower, User $following)
    {
        $follower->unblock($following);

        $follower->follow($following);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }

    public function block(User $blocker, User $blocked)
    {
        $this->unfollow($blocker, $blocked);

        $blocker->block($blocked);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }

    public function unfollow(User $follower, User $following)
    {
        $follower->unfollow($following);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserFollowedEvent($follower, $following));
    }

    public function unblock(User $blocker, User $blocked)
    {
        $blocker->unblock($blocked);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }

    public function create(UserDto $dto, bool $verifyUserEmail = true): User
    {
        $limiter = $this->userRegisterLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $user = new User($dto->email, $dto->username, '');

        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($verifyUserEmail) {
            try {
                $this->bus->dispatch(new UserCreatedMessage($user->getId()));
            } catch (Exception $e) {
            }
        }

        return $user;
    }

    public function edit(User $user, UserDto $dto): User
    {
        $this->entityManager->beginTransaction();
        $mailUpdated = false;

        try {
            if ($dto->avatar) {
                $user->avatar = $dto->avatar;
            }

            if ($dto->plainPassword) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $dto->plainPassword));
            }

            if ($dto->email !== $user->email) {
                $mailUpdated      = true;
                $user->isVerified = false;
                $user->email      = $dto->email;
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        if ($mailUpdated) {
            $this->bus->dispatch(new UserUpdatedMessage($user->getId()));
        }

        return $user;
    }

    public function delete(User $user, bool $purge = false): void
    {
        $this->bus->dispatch(new DeleteUserMessage($user->getId(), $purge));
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

    public function logout(): void
    {
        $this->tokenStorage->setToken(null);
        $this->requestStack->getSession()->invalidate();
    }

    public function attachWallet(User $user, CardanoWalletAddressDto $dto)
    {
        $user->cardanoWalletAddress = $dto->walletAddress;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function ban(User $user)
    {
        $user->isBanned = true;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function unban(User $user)
    {
        $user->isBanned = false;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
