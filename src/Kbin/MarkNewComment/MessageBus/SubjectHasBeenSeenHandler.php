<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\MarkNewComment\MessageBus;

use App\Entity\Entry;
use App\Entity\View;
use App\Kbin\MessageBus\Contracts\AsyncMessageInterface;
use App\Repository\UserRepository;
use App\Repository\ViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
readonly class SubjectHasBeenSeenHandler implements AsyncMessageInterface
{
    public function __construct(
        private ViewRepository $viewRepository,
        private UserRepository $userRepository,
        private CacheInterface $cache,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(SubjectHasBeenSeenMessage $message): void
    {
        $subjectType = Entry::class === $message->subjectType ? 'entry' : 'post';
        $user = $this->userRepository->find($message->userId);
        $subject = $this->entityManager->getRepository($message->subjectType)->find($message->subjectId);

        $entity = $this->viewRepository->findOneBy([
            'user' => $user,
            $subjectType => $subject,
        ]);

        if (!$entity) {
            $entity = new View();
            $entity->user = $user;
            $entity->$subjectType = $subject;
            $entity->lastActive = new \DateTime();
        } else {
            $entity->lastActive = new \DateTime();
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->cache->invalidateTags(['user_view_'.$user->getId()]);
    }
}
