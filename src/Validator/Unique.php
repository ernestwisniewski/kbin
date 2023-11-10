<?php

declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidOptionsException;

/**
 * For this to work when editing something, the DTO must hold the ID
 * of the entity being edited, and the ID mapped using `$idFields`.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Unique extends Constraint
{
    public const NOT_UNIQUE_ERROR = 'eec1b008-c55b-4d91-b5ad-f0b201eb8ada';

    protected const ERROR_NAMES = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    public string $message = 'This value is already used.';

    /**
     * @param non-empty-array<int|string, string> $fields      DTO -> entity field mapping
     * @param array<int|string, string>           $idFields    DTO -> entity ID field mapping
     * @param class-string                        $entityClass
     */
    #[HasNamedArguments]
    public function __construct(
        public string $entityClass,
        public string $errorPath,
        public array $fields,
        public array $idFields = [],
    ) {
        parent::__construct([]);

        if (0 === \count($fields)) {
            throw new InvalidOptionsException('`fields` option must have at least one field', ['fields']);
        }

        if ('' === $this->entityClass) {
            throw new InvalidOptionsException('Bad entity class', ['entityClass']);
        }
    }

    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
