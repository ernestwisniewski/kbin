<?php

declare(strict_types=1);

namespace App\Service\ActivityPub\Webfinger;

use Symfony\Component\HttpFoundation\RequestStack;

class WebFingerParameters
{
    public const REL_KEY_NAME = 'rel';
    public const HOST_KEY_NAME = 'host';
    public const ACCOUNT_KEY_NAME = 'account';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getParams(): array
    {
        $params = [];

        $request = $this->requestStack->getCurrentRequest();

        if ($resource = $request->query->get('resource')) {
            $host = $request->server->get('HTTP_HOST'); // @todo

            if (!str_contains($resource, '//')) {
                $resource = str_replace(':', '://', $resource);
            }

            if (!str_contains($resource, 'acct:')) {
                $resource = 'acct://'.$resource;
            }

            $url = parse_url($resource);
            if (isset($url['scheme']) && isset($url['user']) && isset($url['host'])) {
                $params[static::HOST_KEY_NAME] = $host;

                if ('acct' === $url['scheme']) {
                    if ($host === $url['host']) {
                        $params[static::ACCOUNT_KEY_NAME] = $url['user']; // @todo
                    }
                }
            }
        }

        if ($request->query->has('rel')) {
            $params[static::REL_KEY_NAME] = (array) $request->query->get('rel');
        }

        return $params;
    }
}
