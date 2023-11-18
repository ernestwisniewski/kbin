<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown\Factory;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\MarkdownConverter;

class ConverterFactory
{
    public function createConverter(EnvironmentInterface $environment): ConverterInterface
    {
        return new MarkdownConverter($environment);
    }
}
