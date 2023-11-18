<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\ActivityPub\Ontology;

use ActivityPhp\Type\OntologyBase;

abstract class Mastodon extends OntologyBase
{
    protected static $definitions = [
        'Note' => ['sensitive', 'atomUri', 'inReplyToAtomUri', 'conversation'],
        'Document' => ['blurhash', 'width'],
        'Hashtag' => ['href'],
    ];
}
