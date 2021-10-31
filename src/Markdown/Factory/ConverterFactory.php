<?php declare(strict_types = 1);

namespace App\Markdown\Factory;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\EnvironmentInterface;
use League\CommonMark\MarkdownConverterInterface;

class ConverterFactory
{
    public function createConverter(EnvironmentInterface $environment): MarkdownConverterInterface
    {
        return new CommonMarkConverter([], $environment);
    }
}
