<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use App\Exception\CorruptedFileException;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ImageValidate
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function __invoke(string $filePath): void
    {
        $violations = $this->validator->validate(
            $filePath,
            [
                new Image(['detectCorrupted' => true]),
            ]
        );

        if (\count($violations) > 0) {
            throw new CorruptedFileException();
        }
    }
}
