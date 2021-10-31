<?php declare(strict_types = 1);

namespace App\Validator;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use function count;
use function is_array;
use function is_string;

/**
 * For this to work when editing something, the DTO must hold the ID of the
 * entity being edited, and the ID mapped using `$idFields`.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Unique extends Constraint
{
    public const NOT_UNIQUE_ERROR = 'eec1b008-c55b-4d91-b5ad-f0b201eb8ada';

    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    public $message = 'This value is already used.';

    /**
     * @var string
     */
    public $entityClass;

    /**
     * DTO -> entity field mapping.
     *
     * @var string[]
     */
    public $fields;

    /**
     * DTO -> entity ID field mapping.
     *
     * @var string[]|null
     */
    public $idFields;

    public $errorPath = '';

    public function __construct($options = null)
    {
        parent::__construct($options);

        $fields = $options['fields'] ?? $options['value'];

        if (!is_array($fields) && !is_string($fields)) {
            throw new UnexpectedTypeException($fields, 'array or string');
        }

        $fields = (array) $fields;

        if (count($fields) === 0) {
            throw new InvalidOptionsException(
                'fields option must have at least one field',
                ['fields']
            );
        }

        if (!$options['entityClass']) {
            throw new InvalidOptionsException('Bad entity class', ['entityClass']);
        }
    }

    public function getRequiredOptions(): array
    {
        return ['fields', 'entityClass'];
    }

    public function getDefaultOption(): string
    {
        return 'fields';
    }

    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
