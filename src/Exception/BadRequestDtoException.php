<?php declare(strict_types = 1);

namespace App\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\ConstraintViolationList;

class BadRequestDtoException extends Exception
{
    private ConstraintViolationList $errors;

    #[Pure] public function __construct($message, $errors)
    {
        $this->errors = $errors;

        parent::__construct($message);
    }

    public function getErrors(): ConstraintViolationList
    {
        return $this->errors;
    }
}
