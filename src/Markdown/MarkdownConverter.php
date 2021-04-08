<?php

namespace App\Markdown;

use Psr\EventDispatcher\EventDispatcherInterface;
use App\Markdown\Event\ConvertMarkdown;

class MarkdownConverter
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
    }

    public function convertToHtml(string $markdown, array $context = []): string
    {
        $event = new ConvertMarkdown($markdown);
        $event->mergeAttributes($context);

        $this->dispatcher->dispatch($event);

        return $event->getRenderedHtml();
    }
}
