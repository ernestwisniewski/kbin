<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use JetBrains\PhpStorm\ArrayShape;

class UndoWrapper
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    #[ArrayShape(['@context' => "string", 'id' => "string", 'type' => "string", 'actor' => "mixed", 'object' => "array"])] public function build(
        array $object,
        string $id
    ): array {
        unset($object['@context']);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id'       => $this->urlGenerator->generate('ap_object', ['id' => $id], UrlGeneratorInterface::ABS_URL),
            'type'     => 'Undo',
            'actor'    => $object['actor'],
            'object'   => $object,
        ];
    }
}
