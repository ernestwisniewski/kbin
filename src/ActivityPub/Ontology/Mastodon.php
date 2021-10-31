<?php declare(strict_types = 1);

namespace App\ActivityPub\Ontology;

use ActivityPhp\Type\OntologyBase;

abstract class Mastodon extends OntologyBase
{
    protected static $definitions = [
        'Note'     => ['sensitive', 'atomUri', 'inReplyToAtomUri', 'conversation'],
        'Document' => ['blurhash'],
        'Hashtag'  => ['href'],
    ];
}
