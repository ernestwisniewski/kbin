<?php

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
