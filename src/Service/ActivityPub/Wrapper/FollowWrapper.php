<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use JetBrains\PhpStorm\ArrayShape;

class FollowWrapper
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    #[ArrayShape(['@context' => "string", 'id' => "string", 'actor' => "string", 'object' => "string"])] public function build(
        string $follower,
        string $following,
        string $id
    ): array {
        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id'       => $this->urlGenerator->generate('ap_object', ['id' => $id]),
            'actor'    => $following,
            'object'   => $follower,
        ];
    }
}
