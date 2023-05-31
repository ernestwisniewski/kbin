<?php

declare(strict_types=1);

namespace App\Markdown\Factory;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\EnvironmentInterface;
use League\CommonMark\MarkdownConverterInterface;

class ConverterFactory
{
    public function createConverter(EnvironmentInterface $environment): MarkdownConverterInterface
    {
        return new CommonMarkConverter([
            'renderer' => [
                'soft_break' => "<br>\r\n",
            ],
        ], $environment);
    }
}
