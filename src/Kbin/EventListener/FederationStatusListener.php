<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FederationStatusListener
{
    public function __construct(private bool $kbinFederationEnabled)
    {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest() || $this->kbinFederationEnabled) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if (str_starts_with($route, 'ap_')) {
            throw new NotFoundHttpException();
        }
    }
}
