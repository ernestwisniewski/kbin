<?php

declare(strict_types=1);

namespace App\Markdown\Event;

use App\Markdown\MarkdownConverter;
use App\Markdown\RenderTarget;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConvertMarkdown extends Event
{
    private RenderedContentInterface $renderedContent;
    private array $attributes = [];

    public function __construct(private string $markdown)
    {
    }

    public function getMarkdown(): string
    {
        return $this->markdown;
    }

    public function getRenderedContent(): RenderedContentInterface
    {
        return $this->renderedContent;
    }

    public function setRenderedContent(RenderedContentInterface $renderedContent): void
    {
        $this->renderedContent = $renderedContent;
    }

    public function getRenderTarget(): RenderTarget
    {
        return $this->getAttribute(MarkdownConverter::RENDER_TARGET) ?? RenderTarget::Page;
    }

    /**
     * @return mixed|null
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function addAttribute(string $key, $data): void
    {
        $this->attributes[$key] = $data;
    }

    public function mergeAttributes(array $attributes): void
    {
        $this->attributes = array_replace($this->attributes, $attributes);
    }

    public function removeAttribute(string $key): void
    {
        unset($this->attributes[$key]);
    }
}
