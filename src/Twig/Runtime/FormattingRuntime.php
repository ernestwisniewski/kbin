<?php declare(strict_types = 1);

namespace App\Twig\Runtime;

use App\Markdown\MarkdownConverter;
use Twig\Extension\RuntimeExtensionInterface;

class FormattingRuntime implements RuntimeExtensionInterface
{
    public function __construct(private MarkdownConverter $markdownConverter)
    {
    }

    public function convertToHtml(string $value): string
    {
        return $this->markdownConverter->convertToHtml($value);
    }

    public function getShortDesc(string $val): string
    {
        $subject = array_filter(explode('.', $val));

        $sentences = $subject[0].'.';
        if (isset($subject[1])) {
            $sentences .= $subject[1];
        }

        return $sentences . '.';
    }
}
