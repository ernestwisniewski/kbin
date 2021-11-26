<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CloudflareIpResolver
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    public function resolve(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        return $request->server->get('HTTP_CF_CONNECTING_IP') ?? $request->getClientIp();
    }
}
