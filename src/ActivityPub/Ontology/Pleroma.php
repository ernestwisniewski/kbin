<?php declare(strict_types=1);

namespace App\ActivityPub\Ontology;

use ActivityPhp\Type\OntologyBase;

abstract class Pleroma extends OntologyBase
{
    protected static $definitions = [
        'Person'   => ['alsoKnownAs', 'capabilities', 'vcard:Address', 'vcard:bday'],
        'Create'   => ['context_id', 'directMessage'],
        'Note'     => ['actor', 'repliesCount', 'quoteUri', 'quoteUrl', 'formerRepresentations', 'quotesCount'],
        'Question' => ['conversation', 'sensitive', 'voters', 'repliesCount'],
    ];
}
