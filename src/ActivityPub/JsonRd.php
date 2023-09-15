<?php

declare(strict_types=1);

namespace App\ActivityPub;

/**
 * Class JsonRd.
 *
 * The JSON Resource Descriptor (JRD), originally introduced in RFC 6415
 * [https://tools.ietf.org/html/rfc7033#ref-16] and based on the Extensible
 * Resource Descriptor (XRD) format
 * [https://tools.ietf.org/html/rfc7033#ref-17], is a JSON object that
 * comprises the following name/value pairs:
 *
 * - subject
 * - aliases
 * - properties
 * - links
 *
 * The member "subject" is a name/value pair whose value is a string,
 * "aliases" is an array of strings, "properties" is an object
 * comprising name/value pairs whose values are strings, and "links" is
 * an array of objects that contain link relation information.
 *
 * When processing a JRD, the client MUST ignore any unknown member and
 * not treat the presence of an unknown member as an error.
 *
 * Forked from https://github.com/delirehberi/webfinger,
 *
 * @see https://github.com/delirehberi/webfinger/blob/master/src/JsonRD.php
 */
class JsonRd
{
    /**
     * The value of the "subject" member is a URI that identifies the entity
     * that the JRD describes.
     *
     * The "subject" value returned by a WebFinger resource MAY differ from
     * the value of the "resource" parameter used in the client's request.
     * This might happen, for example, when the subject's identity changes
     * (e.g., a user moves his or her account to another service) or when
     * the resource prefers to express URIs in canonical form.
     *
     * The "subject" member SHOULD be present in the JRD.
     *
     * @var string
     */
    protected $subject = '';

    /**
     * The "aliases" array is an array of zero or more URI strings that
     * identify the same entity as the "subject" URI.
     * The "aliases" array is OPTIONAL in the JRD.
     *
     * @var array[string]
     */
    protected $aliases = [];

    /**
     * The "properties" object comprises zero or more name/value pairs whose
     * names are URIs (referred to as "property identifiers") and whose
     * values are strings or null.  Properties are used to convey additional
     * information about the subject of the JRD.  As an example, consider
     * this use of "properties":.
     *
     * "properties" : { "http://webfinger.example/ns/name" : "Bob Smith" }
     *
     * The "properties" member is OPTIONAL in the JRD.
     *
     * @var array[string=>string]
     */
    protected $properties = [];

    /**
     * The "links" array has any number of member objects, each of which
     * represents a link [4].
     *
     * @var array[JsonRdLink]
     */
    protected $links = [];

    public function addAlias(string $uri): JsonRd
    {
        array_push($this->aliases, $uri);

        return $this;
    }

    public function removeAlias(string $uri): JsonRd
    {
        $key = array_search($uri, $this->aliases);
        if (false !== $key) {
            unset($this->aliases[$key]);
        }

        return $this;
    }

    public function addProperty(string $uri, string $value = null): JsonRd
    {
        $this->properties[$uri] = $value;

        return $this;
    }

    public function removeProperty(string $uri): JsonRd
    {
        if (!\array_key_exists($uri, $this->properties)) {
            return $this;
        }
        unset($this->properties[$uri]);

        return $this;
    }

    public function addLink(JsonRdLink $link): JsonRd
    {
        array_push($this->links, $link);

        return $this;
    }

    public function removeLink(JsonRdLink $link): JsonRd
    {
        $serialized_link = serialize($link);
        foreach ($this->links as $key => $_link) {
            $_serialized_link = serialize($_link);
            if ($_serialized_link === $serialized_link) {
                unset($this->links[$key]);
                break;
            }
        }

        return $this;
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        $data = [];
        if (!empty($this->getSubject())) {
            $data['subject'] = $this->getSubject();
        }
        !empty($this->getAliases()) && $data['aliases'] = $this->getAliases();
        !empty($this->getLinks()) && $data['links'] = array_map(function (JsonRdLink $jsonRdLink) {
            return $jsonRdLink->toArray();
        }, $this->getLinks());
        !empty($this->getProperties()) && $data['properties'] = $this->getProperties();

        return $data;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject(string $subject): JsonRd
    {
        $this->subject = $subject;

        return $this;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    protected function setAliases(array $aliases): JsonRd
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    protected function setLinks(array $links): JsonRd
    {
        $this->links = $links;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    protected function setProperties(array $properties): JsonRd
    {
        $this->properties = $properties;

        return $this;
    }
}
