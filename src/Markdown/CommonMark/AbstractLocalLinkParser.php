<?php declare(strict_types=1);

namespace App\Markdown\CommonMark;

use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;

/**
 * Parses links like /u/foo, w/bar, etc.
 */
abstract class AbstractLocalLinkParser implements InlineParserInterface
{
    final public function getCharacters(): array
    {
        return ['/', $this->getPrefix()];
    }

    /**
     * Return a single-character prefix.
     */
    abstract public function getPrefix(): string;

    protected function kbinPrefix(): bool
    {
        return true;
    }

    final public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        $previousChar = $cursor->peek(-1);

        if ($previousChar !== null && !preg_match('!^\s+$!', $previousChar)) {
            return false;
        }

        $previousState = $cursor->saveState();

        $prefix = $this->kbinPrefix() ? $cursor->match('@^/?'.$this->getPrefix().'/@') : $this->getPrefix();

        if ($prefix === null) {
            return false;
        }

        $name = $cursor->match($this->getRegex());

        if ($name === null) {
            $cursor->restoreState($previousState);

            return false;
        }

        $link = new Link($this->getUrl($this->kbinPrefix() ? $name : substr($name, 1)), ($this->kbinPrefix() ? $prefix : '').$name);

        $inlineContext->getContainer()->appendChild($link);

        return true;
    }

    abstract public function getRegex(): string;

    /**
     * Generates a URL based on the extracted suffix.
     */
    abstract public function getUrl(string $suffix): string;
}
