<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class IpResolver
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function resolve(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        if ($fastly = $request->server->get('HTTP_FASTLY_CLIENT_IP')) {
            return $fastly;
        }

        return $request->server->get('HTTP_CF_CONNECTING_IP') ?? $request->getClientIp();
    }
}
