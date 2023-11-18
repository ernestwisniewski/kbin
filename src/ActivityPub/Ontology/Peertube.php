<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\ActivityPub\Ontology;

use ActivityPhp\Type\OntologyBase;

abstract class Peertube extends OntologyBase
{
    protected static $definitions = [
        'Group' => ['support'],
        'Person' => ['featured', 'featuredTags', 'manuallyApprovesFollowers', 'discoverable', 'devices'],
        'Video' => [
            'uuid',
            'category',
            'language',
            'views',
            'sensitive',
            'waitTranscoding',
            'state',
            'commentsEnabled',
            'support',
            'subtitleLanguage',
            'likes',
            'dislikes',
            'shares',
            'comments',
            'licence',
            'downloadEnabled',
            'originallyPublishedAt',
            'isLiveBroadcast',
            'liveSaveReplay',
            'permanentLive',
        ],
        'Image' => ['width', 'height'],
        'Link' => ['fps', 'mimeType', 'size', 'tag'],
        'Hashtag' => ['type'],
        'Person|Group' => ['uuid', 'publicKey', 'playlists'],
    ];
}
