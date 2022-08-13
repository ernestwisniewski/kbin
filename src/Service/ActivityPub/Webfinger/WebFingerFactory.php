<?php

/*
 * This file is part of the ActivityPhp package.
 *
 * Copyright (c) landrok at github.com/landrok
 *
 * For the full copyright and license information, please see
 * <https://github.com/landrok/activitypub/blob/master/LICENSE>.
 */

namespace App\Service\ActivityPub\Webfinger;

use ActivityPhp\Server;
use App\Service\ActivityPub\ApHttpClient;
use Exception;

/**
 * A simple WebFinger discoverer tool
 */
class WebFingerFactory
{
    public function __construct(private ApHttpClient $client)
    {

    }

    const WEBFINGER_URL = '%s://%s%s/.well-known/webfinger?resource=acct:%s';

    protected static $server;

    protected array $webfingers = [];

    public function get(string $handle, string $scheme = 'https')
    {
        if (!preg_match(
            '/^@?(?P<user>[\w\-\.]+)@(?P<host>[\w\.\-]+)(?P<port>:[\d]+)?$/',
            $handle,
            $matches
        )
        ) {
            throw new Exception(
                "WebFinger handle is malformed '{$handle}'"
            );
        }

        // Unformat Mastodon handle @user@host => user@host
        $handle = strpos($handle, '@') === 0
            ? substr($handle, 1) : $handle;

        // Build a WebFinger URL
        $url = sprintf(
            self::WEBFINGER_URL,
            $scheme,
            $matches['host'],
            isset($matches['port']) ? $matches['port'] : '',
            $handle
        );

        $content = $this->client->getActorObject($url);

        if (!is_array($content) || !count($content)) {
            throw new Exception('WebFinger fetching has failed');
        }

        $this->webfingers[$handle] = new WebFinger($content);

        return $this->webfingers[$handle];
    }

    /**
     * Inject a server instance
     *
     * @param \ActivityPhp\Server $server
     */
    public static function setServer(Server $server)
    {
        self::$server = $server;
    }
}
