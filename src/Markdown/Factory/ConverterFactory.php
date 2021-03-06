<?php

namespace App\Markdown\Factory;

use League\CommonMark\MarkdownConverterInterface;
use League\CommonMark\EnvironmentInterface;
use League\CommonMark\CommonMarkConverter;

class ConverterFactory
{
    public function createConverter(EnvironmentInterface $environment): MarkdownConverterInterface
    {
        return new CommonMarkConverter([], $environment);
    }
}
