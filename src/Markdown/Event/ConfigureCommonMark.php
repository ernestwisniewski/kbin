<?php

declare(strict_types=1);

namespace App\Markdown\Event;

use League\CommonMark\ConfigurableEnvironmentInterface;

class ConfigureCommonMark
{
    public function __construct(
        private readonly ConfigurableEnvironmentInterface $environment,
        private readonly ConvertMarkdown $convertMarkdownEvent
    ) {
    }

    public function getEnvironment(): ConfigurableEnvironmentInterface
    {
        return $this->environment;
    }

    public function getConvertMarkdownEvent(): ConvertMarkdown
    {
        return $this->convertMarkdownEvent;
    }
}
