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

use App\Exception\InvalidWebfingerException;

/**
 * A simple WebFinger container of data.
 */
class WebFinger
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string[]
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $links = [];

    /**
     * Construct WebFinger instance.
     *
     * @param array $data A WebFinger response
     */
    public function __construct(array $data)
    {
        $data['aliases'] = [];

        foreach (['subject', 'aliases', 'links'] as $key) {
            if (!isset($data[$key])) {
                throw new \Exception("WebFinger profile must contain '$key' property");
            }
            $method = 'set'.ucfirst($key);
            $this->$method($data[$key]);
        }
    }

    /**
     * Get ActivityPhp profile id URL.
     *
     * @return string
     * @throws InvalidWebfingerException
     */
    public function getProfileId()
    {
        foreach ($this->links as $link) {
            if (isset($link['rel'], $link['type'], $link['href'])) {
                if ('self' == $link['rel']
                    && 'application/activity+json' == $link['type']
                ) {
                    return $link['href'];
                }
            }
        }

        throw new InvalidWebfingerException();
    }

    /**
     * Get WebFinger response as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'subject' => $this->subject,
            'aliases' => $this->aliases,
            'links' => $this->links,
        ];
    }

    /**
     * Get aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Set aliases property.
     */
    protected function setAliases(array $aliases)
    {
        foreach ($aliases as $alias) {
            if (!is_string($alias)) {
                throw new \Exception('WebFinger aliases must be an array of strings');
            }

            $this->aliases[] = $alias;
        }
    }

    /**
     * Get links.
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set links property.
     */
    protected function setLinks(array $links)
    {
        foreach ($links as $link) {
            if (!is_array($link)) {
                throw new \Exception('WebFinger links must be an array of objects');
            }

            if (!isset($link['rel'])) {
                throw new \Exception("WebFinger links object must contain 'rel' property");
            }

            $tmp = [];
            $tmp['rel'] = $link['rel'];

            foreach (['type', 'href', 'template'] as $key) {
                if (isset($link[$key]) && is_string($link[$key])) {
                    $tmp[$key] = $link[$key];
                }
            }

            $this->links[] = $tmp;
        }
    }

    /**
     * Get subject fetched from profile.
     *
     * @return string|null Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set subject property.
     *
     * @param string $subject
     */
    protected function setSubject($subject)
    {
        if (!is_string($subject)) {
            throw new \Exception('WebFinger subject must be a string');
        }

        $this->subject = $subject;
    }

    /**
     * Get subject handle fetched from profile.
     *
     * @return string|null
     */
    public function getHandle()
    {
        return substr($this->subject, 5);
    }

    public function getProfileIds(): array
    {
        $urls = [];
        foreach ($this->links as $link) {
            if (isset($link['rel'], $link['type'], $link['href'])) {
                if ('self' == $link['rel']
                    && 'application/activity+json' == $link['type']
                ) {
                    $urls[] = $link['href'];
                }
            }
        }

        return $urls;
    }
}
