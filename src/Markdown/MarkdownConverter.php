<?php declare(strict_types = 1);

namespace App\Markdown;

use App\Markdown\Event\ConvertMarkdown;
use Psr\EventDispatcher\EventDispatcherInterface;

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
