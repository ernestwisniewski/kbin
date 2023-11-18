<?php

declare(strict_types=1);

// Copyright (c) landrok at github.com/landrok
// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Service\ActivityPub\Webfinger;

use ActivityPhp\Server;
use App\Service\ActivityPub\ApHttpClient;

/**
 * A simple WebFinger discoverer tool.
 */
class WebFingerFactory
{
    public const WEBFINGER_URL = '%s://%s%s/.well-known/webfinger?resource=acct:%s';
    protected static $server;
    protected array $webfingers = [];

    public function __construct(private readonly ApHttpClient $client)
    {
    }

    /**
     * Inject a server instance.
     */
    public static function setServer(Server $server)
    {
        self::$server = $server;
    }

    public function get(string $handle, string $scheme = 'https')
    {
        if (!preg_match(
            '/^@?(?P<user>[\w\-\.]+)@(?P<host>[\w\.\-]+)(?P<port>:[\d]+)?$/',
            $handle,
            $matches
        )
        ) {
            throw new \Exception("WebFinger handle is malformed '{$handle}'");
        }

        // Unformat Mastodon handle @user@host => user@host
        $handle = 0 === strpos($handle, '@')
            ? substr($handle, 1) : $handle;

        // Build a WebFinger URL
        $url = sprintf(
            self::WEBFINGER_URL,
            $scheme,
            $matches['host'],
            isset($matches['port']) ? $matches['port'] : '',
            $handle
        );

        $content = $this->client->getWebfingerObject($url);

        if (!\is_array($content) || !\count($content)) {
            throw new \Exception('WebFinger fetching has failed');
        }

        $this->webfingers[$handle] = new WebFinger($content);

        return $this->webfingers[$handle];
    }
}
