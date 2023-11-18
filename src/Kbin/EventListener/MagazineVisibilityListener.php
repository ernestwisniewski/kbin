<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EventListener;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class MagazineVisibilityListener
{
    public function __construct(private Security $security)
    {
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $magazine = array_filter($event->getArguments(), fn ($argument) => $argument instanceof Magazine);

        if (!$magazine) {
            return;
        }

        $magazine = array_values($magazine)[0];

        if (VisibilityInterface::VISIBILITY_VISIBLE !== $magazine->getVisibility()) {
            if (null === $this->security->getUser()
                || false === $magazine->userIsOwner($this->security->getUser())
                && false === $this->security->isGranted('ROLE_ADMIN')) {
                throw new NotFoundHttpException();
            }
        }
    }
}
