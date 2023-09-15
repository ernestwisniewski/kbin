<?php

declare(strict_types=1);

namespace App\ActivityPub;

/**
 * Class JsonRDLinks.
 *
 * The "links" array has any number of member objects, each of which
 * represents a link [https://tools.ietf.org/html/rfc7033#ref-4].  Each of these link objects can have the
 * following members:
 *
 * o rel
 * o type
 * o href
 * o titles
 * o properties
 *
 * The "rel" and "href" members are strings representing the link's
 * relation type and the target URI, respectively.  The context of the
 * link is the "subject" (see Section 4.4.1).
 *
 * The "type" member is a string indicating what the media type of the
 * result of dereferencing the link ought to be.
 *
 * The order of elements in the "links" array MAY be interpreted as
 * indicating an order of preference.  Thus, if there are two or more
 * link relations having the same "rel" value, the first link relation
 * would indicate the user's preferred link.
 *
 * The "links" array is OPTIONAL in the JRD.
 *
 * Below, each of the members of the objects found in the "links" array
 * is described in more detail.  Each object in the "links" array,
 * referred to as a "link relation object", is completely independent
 * from any other object in the array; any requirement to include a
 * given member in the link relation object refers only to that
 * particular object.
 *
 * Forked from https://github.com/delirehberi/webfinger,
 *
 * @see https://github.com/delirehberi/webfinger/blob/master/src/JsonRDLink.php
 */
class JsonRdLink
{
    /**
     * Link Relation Types
     * Registration Procedure(s)
     * Specification Required
     * Expert(s)
     * Mark Nottingham, Julian Reschke, Jan Algermissen
     * Reference
     * [http://www.iana.org/go/rfc8288]
     * Note
     * New link relations, along with changes to existing relations, can be requested
     * using the [https://github.com/link-relations/registry] or the mailing list defined in [RFC8288].
     */
    public const REGISTERED_RELATION_TYPES = [
        'about',
        'alternate',
        'appendix',
        'archives',
        'author',
        'blocked-by',
        'bookmark',
        'canonical',
        'chapter',
        'cite-as',
        'collection',
        'contents',
        'convertedFrom',
        'copyright',
        'create-form',
        'current',
        'describedby',
        'describes',
        'disclosure',
        'dns-prefetch',
        'duplicate',
        'edit',
        'edit-form',
        'edit-media',
        'enclosure',
        'first',
        'glossary',
        'help',
        'hosts',
        'hub',
        'icon',
        'index',
        'item',
        'last',
        'latest-version',
        'license',
        'lrdd',
        'memento',
        'monitor',
        'monitor-group',
        'next',
        'next-archive',
        'nofollow',
        'noreferrer',
        'original',
        'payment',
        'pingback',
        'preconnect',
        'predecessor-version',
        'prefetch',
        'preload',
        'prerender',
        'prev',
        'preview',
        'previous',
        'prev-archive',
        'privacy-policy',
        'profile',
        'related',
        'restconf',
        'replies',
        'search',
        'section',
        'self',
        'service',
        'start',
        'stylesheet',
        'subsection',
        'successor-version',
        'tag',
        'terms-of-service',
        'timegate',
        'timemap',
        'type',
        'up',
        'version-history',
        'via',
        'webmention',
        'working-copy',
        'working-copy-of',
    ];

    /**
     * The value of the "rel" member is a string that is either a URI or a
     * registered relation type [https://tools.ietf.org/html/rfc7033#ref-8]
     * (see RFC 5988 [https://tools.ietf.org/html/rfc7033#ref-4]).  The value
     * of the "rel" member MUST contain exactly one URI or registered relation
     * type. The URI or registered relation type identifies the type of the
     * link relation.
     *
     * The other members of the object have meaning only once the type of
     * link relation is understood.  In some instances, the link relation
     * will have associated semantics enabling the client to query for other
     * resources on the Internet.  In other instances, the link relation
     * will have associated semantics enabling the client to utilize the
     * other members of the link relation object without fetching additional
     * external resources.
     *
     * URI link relation type values are compared using the "Simple String
     * Comparison" algorithm of Section 6.2.1 of RFC 3986.
     *
     * The "rel" member MUST be present in the link relation object.
     *
     * @var string
     */
    protected $rel = '';
    /**
     * The value of the "type" member is a string that indicates the media
     * type [https://tools.ietf.org/html/rfc7033#ref-9] of the target resource (see RFC 6838
     * [https://tools.ietf.org/html/rfc7033#ref-10]).
     * The "type" member is OPTIONAL in the link relation object.
     *
     * @var string
     */
    protected $type = '';

    /**
     * The value of the "href" member is a string that contains a URI
     * pointing to the target resource.
     * The "href" member is OPTIONAL in the link relation object.
     *
     * @var string
     */
    protected $href;

    /**
     * The "titles" object comprises zero or more name/value pairs whose
     * names are a language tag [11] or the string "und".  The string is
     * human-readable and describes the link relation.  More than one title
     * for the link relation MAY be provided for the benefit of users who
     * utilize the link relation, and, if used, a language identifier SHOULD
     * be duly used as the name.  If the language is unknown or unspecified,
     * then the name is "und".
     *
     * A JRD SHOULD NOT include more than one title identified with the same
     * language tag (or "und") within the link relation object.  Meaning is
     * undefined if a link relation object includes more than one title
     * named with the same language tag (or "und"), though this MUST NOT be
     * treated as an error.  A client MAY select whichever title or titles
     * it wishes to utilize.
     *
     * Here is an example of the "titles" object:
     *
     * "titles" :
     *   {
     *   "en-us" : "The Magical World of Steve",
     *   "fr" : "Le Monde Magique de Steve"
     *   }
     *
     * The "titles" member is OPTIONAL in the link relation object.
     *
     * @var array[string=>string]
     */
    protected $titles = [];

    /**
     * The "properties" object within the link relation object comprises
     * zero or more name/value pairs whose names are URIs (referred to as
     * "property identifiers") and whose values are strings or null.
     * Properties are used to convey additional information about the link
     * relation.  As an example, consider this use of "properties":.
     *
     * "properties" : { "http://webfinger.example/mail/port" : "993" }
     *
     * The "properties" member is OPTIONAL in the link relation object.
     *
     * @var array[string=>string]
     */
    protected $properties = [];

    public function addTitle(string $locale, string $value): JsonRdLink
    {
        if (!\array_key_exists($locale, $this->titles)) {
            $this->titles[$locale] = $value;
        }

        return $this;
    }

    public function removeTitle(string $locale): JsonRdLink
    {
        if (!\array_key_exists($locale, $this->titles)) {
            return $this;
        }
        unset($this->titles[$locale]);

        return $this;
    }

    public function addProperty(string $url, string $value): JsonRdLink
    {
        $this->properties[$url] = $value;

        return $this;
    }

    public function removeProperty(string $url): JsonRdLink
    {
        if (!\array_key_exists($url, $this->properties)) {
            return $this;
        }
        unset($this->properties[$url]);

        return $this;
    }

    public function toArray(): array
    {
        $data = [];
        $data['rel'] = $this->getRel();
        $data['href'] = $this->getHref();

        !empty($this->getType()) && $data['type'] = $this->getType();
        !empty($this->getTitles()) && $data['titles'] = $this->getTitles();
        !empty($this->getProperties()) && $data['properties'] = $this->getProperties();

        return $data;
    }

    public function getRel(): string
    {
        return $this->rel;
    }

    /**
     * @throws \Exception
     */
    public function setRel(string $relation): JsonRdLink
    {
        if (\in_array($relation, self::REGISTERED_RELATION_TYPES)) {
            $this->rel = $relation;

            return $this;
        }
        preg_match("/^http(s)?\:\/\/[a-z]+\.[a-z]+/", $relation, $match);
        if (isset($match[0]) && !empty($match[0])) {
            $this->rel = $relation;

            return $this;
        }
        throw new \Exception('The value of the `rel` member MUST contain exactly one URI or registered relation type.');
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    /**
     * @todo we need to write for url validation for $href argument.
     */
    public function setHref(string $href): JsonRdLink
    {
        $this->href = $href;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): JsonRdLink
    {
        $this->type = $type;

        return $this;
    }

    public function getTitles(): array
    {
        return $this->titles;
    }

    protected function setTitles(array $titles): JsonRdLink
    {
        $this->titles = $titles;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    protected function setProperties(array $properties): JsonRdLink
    {
        $this->properties = $properties;

        return $this;
    }
}
