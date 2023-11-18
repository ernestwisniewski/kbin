<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Kbin\User\DTO\UserDto;
use App\Message\DeleteImageMessage;
use App\Message\UserUpdatedMessage;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserEdit
{
    public function __construct(
        private ImageRepository $imageRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, UserDto $dto): User
    {
        $this->entityManager->beginTransaction();
        $mailUpdated = false;

        try {
            $user->about = $dto->about;

            $oldAvatar = $user->avatar;
            if ($dto->avatar) {
                $image = $this->imageRepository->find($dto->avatar->id);
                $user->avatar = $image;
            }

            $oldCover = $user->cover;
            if ($dto->cover) {
                $image = $this->imageRepository->find($dto->cover->id);
                $user->cover = $image;
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

            if ($this->security->isGranted('edit_profile', $user)
                && !$user->isTotpAuthenticationEnabled()
                && $dto->totpSecret) {
                $user->setTotpSecret($dto->totpSecret);
            }

            $user->lastActive = new \DateTime();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        if ($oldAvatar && $user->avatar !== $oldAvatar) {
            $this->messageBus->dispatch(new DeleteImageMessage($oldAvatar->filePath));
        }

        if ($oldCover && $user->cover !== $oldCover) {
            $this->messageBus->dispatch(new DeleteImageMessage($oldCover->filePath));
        }

        if ($mailUpdated) {
            $this->messageBus->dispatch(new UserUpdatedMessage($user->getId()));
        }

        return $user;
    }
}
