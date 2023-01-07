<?php

declare(strict_types=1);

namespace App\ActivityPub\Ontology;

use ActivityPhp\Type\OntologyBase;

abstract class Lemmy extends OntologyBase
{
    protected static $definitions = [
        'Person' => ['matrixUserId'],
    ];
}
