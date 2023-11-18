<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Exception;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\ConstraintViolationList;

class BadRequestDtoException extends \Exception
{
    private ConstraintViolationList $errors;

    #[Pure]
    public function __construct($message, $errors)
    {
        $this->errors = $errors;

        parent::__construct($message);
    }

    public function getErrors(): ConstraintViolationList
    {
        return $this->errors;
    }
}
