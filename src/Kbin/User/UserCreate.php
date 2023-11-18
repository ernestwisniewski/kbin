<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Kbin\User\DTO\UserDto;
use App\Message\UserCreatedMessage;
use App\Service\ActivityPub\KeysGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class UserCreate
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
        private RateLimiterFactory $userRegisterLimiter,
    ) {
    }

    public function __invoke(UserDto $dto, bool $verifyUserEmail = true, $rateLimit = true): User
    {
        if ($rateLimit) {
            $limiter = $this->userRegisterLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $user = new User($dto->email, $dto->username, '', $dto->apProfileId, $dto->apId);

        $user->isBot = true === $dto->isBot;

        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->plainPassword));

        if (!$dto->apId) {
            $user = KeysGenerator::generate($user);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($verifyUserEmail) {
            try {
                $this->messageBus->dispatch(new UserCreatedMessage($user->getId()));
            } catch (\Exception $e) {
            }
        }

        return $user;
    }
}
