<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Markdown\MarkdownConverter;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class FormattingRuntime implements RuntimeExtensionInterface
{
    private MarkdownConverter $markdownConverter;

    public function __construct(MarkdownConverter $markdownConverter)
    {
        $this->markdownConverter = $markdownConverter;
    }

    public function convertToHtml(string $value): string
    {
        return $this->markdownConverter->convertToHtml($value);
    }
}
