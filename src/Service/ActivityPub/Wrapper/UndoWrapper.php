<?php

declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use JetBrains\PhpStorm\ArrayShape;

class UndoWrapper
{
    #[ArrayShape([
        '@context' => 'string',
        'id' => 'string',
        'type' => 'string',
        'actor' => 'mixed',
        'object' => 'array',
    ])]
    public function build(
        array $object,
    ): array {
        unset($object['@context']);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $object['id'].'#unfollow',
            'type' => 'Undo',
            'actor' => $object['actor'],
            'object' => $object,
        ];
    }
}
