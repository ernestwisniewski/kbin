<?php declare(strict_types = 1);

namespace App\Markdown\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ConvertMarkdown extends Event
{
    private string $renderedHtml = '';
    private array $attributes = [];

    public function __construct(private string $markdown)
    {
    }

    public function getMarkdown(): string
    {
        return $this->markdown;
    }

    public function setMarkdown(string $markdown): void
    {
        $this->markdown = $markdown;
    }

    public function getRenderedHtml(): string
    {
        return $this->renderedHtml;
    }

    public function setRenderedHtml(string $renderedHtml): void
    {
        $this->renderedHtml = $renderedHtml;
    }

    /**
     * @return mixed|null
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @param mixed $data
     */
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
