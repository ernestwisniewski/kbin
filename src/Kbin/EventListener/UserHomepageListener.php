<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class UserHomepageListener
{
    public function __construct(private Security $security, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || '/' !== $event->getRequest()->getPathInfo()) {
            return;
        }

        $user = $this->security->getUser();

        if ($user instanceof User && User::HOMEPAGE_ALL !== $user->homepage) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate($user->homepage)));
        }
    }
}
