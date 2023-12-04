<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

readonly class UserActivityListener
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[NoReturn]
    public function onKernelController(ControllerEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        if ($this->security->getToken()) {
            $user = $this->security->getToken()->getUser();

            if (($user instanceof User) && !$user->isActiveNow()) {
                $user->lastActive = new \DateTime();
                $this->entityManager->flush();
            }
        }
    }
}
