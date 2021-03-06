<?php

namespace App\Markdown\Event;

use League\CommonMark\ConfigurableEnvironmentInterface;

class ConfigureCommonMark
{
    private ConfigurableEnvironmentInterface $environment;
    private ConvertMarkdown $convertMarkdownEvent;

    public function __construct(
        ConfigurableEnvironmentInterface $environment,
        ConvertMarkdown $convertMarkdownEvent
    ) {
        $this->environment = $environment;
        $this->convertMarkdownEvent = $convertMarkdownEvent;
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
