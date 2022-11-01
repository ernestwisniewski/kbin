<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\CardanoWalletAddressDto;
use App\DTO\UserDto;
use App\Entity\User;
use App\Entity\UserFollowRequest;
use App\Event\User\UserBlockEvent;
use App\Event\User\UserFollowEvent;
use App\Factory\UserFactory;
use App\Message\DeleteImageMessage;
use App\Message\DeleteUserMessage;
use App\Message\UserCreatedMessage;
use App\Message\UserUpdatedMessage;
use App\Repository\UserFollowRepository;
use App\Repository\UserFollowRequestRepository;
use App\Security\EmailVerifier;
use App\Service\ActivityPub\KeysGenerator;
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
use Symfony\Component\Security\Core\Security;
use Tchoulom\ViewCounterBundle\Util\Date;

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
        private UserFollowRequestRepository $requestRepository,
        private UserFollowRepository $userFollowRepository,
        private Security $security,
    ) {
    }

    public function follow(User $follower, User $following, $createRequest = true): void
    {
        if ($following->apManuallyApprovesFollowers && $createRequest) {
            if ($this->requestRepository->findOneby(['follower' => $follower, 'following' => $following])) {
                return;
            }

            $request = new UserFollowRequest($follower, $following);
            $this->entityManager->persist($request);
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new UserFollowEvent($follower, $following));

            return;
        }

        $follower->unblock($following);

        $follower->follow($following);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserFollowEvent($follower, $following));
    }

    public function acceptFollow(User $follower, User $following): void
    {
        if ($request = $this->requestRepository->findOneby(['follower' => $follower, 'following' => $following])) {
            $this->entityManager->remove($request);
        }

        if ($this->userFollowRepository->findOneBy(['follower' => $follower, 'following' => $following])) {
            return;
        }

        $this->follow($follower, $following, false);
    }

    public function rejectFollow(User $follower, User $following): void
    {
        if ($request = $this->requestRepository->findOneby(['follower' => $follower, 'following' => $following])) {
            $this->entityManager->remove($request);
            $this->entityManager->flush();
        }
    }

    public function block(User $blocker, User $blocked): void
    {
        $this->unfollow($blocker, $blocked);

        $blocker->block($blocked);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }

    public function unfollow(User $follower, User $following): void
    {
        if ($request = $this->requestRepository->findOneby(['follower' => $follower, 'following' => $following])) {
            $this->entityManager->remove($request);
        }

        $follower->unfollow($following);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserFollowEvent($follower, $following, true));
    }

    public function unblock(User $blocker, User $blocked): void
    {
        $blocker->unblock($blocked);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }

    public function create(UserDto $dto, bool $verifyUserEmail = true, $limiter = true): User
    {
        if ($limiter) {
            $limiter = $this->userRegisterLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $user = new User($dto->email, $dto->username, '', $dto->apProfileId, $dto->apId);

        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->plainPassword));

        if (!$dto->apId) {
            $user = KeysGenerator::generate($user);
        }

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
            $user->about = $dto->about;

            $oldAvatar = $user->avatar;
            if ($dto->avatar) {
                $user->avatar = $dto->avatar;
            }

            $oldCover = $user->cover;
            if ($dto->cover) {
                $user->cover = $dto->cover;
            }

            if ($dto->plainPassword) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $dto->plainPassword));
            }

            if ($dto->email !== $user->email) {
                $mailUpdated = true;
                $user->isVerified = false;
                $user->email = $dto->email;
            }

            if ($this->security->isGranted('edit_profile', $user)) {
                $user->username = $dto->username;
            }

            $user->lastActive = new \DateTime();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        if ($oldAvatar && $user->avatar !== $oldAvatar) {
            $this->bus->dispatch(new DeleteImageMessage($oldAvatar->filePath));
        }

        if ($oldCover && $user->cover !== $oldCover) {
            $this->bus->dispatch(new DeleteImageMessage($oldCover->filePath));
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

    public function attachWallet(User $user, CardanoWalletAddressDto $dto): void
    {
        $user->cardanoWalletAddress = $dto->walletAddress;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function ban(User $user): void
    {
        $user->isBanned = true;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function unban(User $user): void
    {
        $user->isBanned = false;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function detachAvatar(User $user): void
    {
        $image = $user->avatar->filePath;

        $user->avatar = null;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }

    public function detachCover(User $user): void
    {
        $image = $user->cover->filePath;

        $user->cover = null;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }
}
