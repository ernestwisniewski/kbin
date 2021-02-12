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
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Event\UserFollowedEvent;
use App\DTO\RegisterUserDto;
use App\DTO\UserDto;
use App\Entity\User;

class UserManager
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private EventDispatcherInterface $eventDispatcher;
    private MessageBusInterface $messageBus;
    private EmailVerifier $emailVerifier;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EventDispatcherInterface $eventDispatcher,
        MessageBusInterface $messageBus,
        EmailVerifier $emailVerifier,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus      = $messageBus;
        $this->emailVerifier   = $emailVerifier;
        $this->userRepository  = $userRepository;
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


    public function create(RegisterUserDto $dto): User
    {
        $user = new User($dto->getEmail(), $dto->getUsername(), '');

        $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->getPlainPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new UserCreatedMessage($user->getId()));

        return $user;
    }

    public function edit(User $user, UserDto $dto): User
    {
        if ($dto->getPlainPassword()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->getPlainPassword()));

            $this->entityManager->flush();
        }

        if($dto->getEmail() !== $user->getEmail()) {
            $user->setIsVerified(false);
            $user->setEmail($dto->getEmail());

            $this->entityManager->flush();

            $this->messageBus->dispatch(new UserUpdatedMessage($user->getId()));
        }

        return $user;
    }

    public function createDto(User $user): UserDto
    {
        $dto = new UserDto();

        $dto->setId($user->getId());
        $dto->setUsername($user->getUsername());
        $dto->setEmail($user->getEmail());

        return $dto;
    }

    public function verify(Request $request, User $user): void
    {
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }
}
