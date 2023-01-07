<?php

declare(strict_types=1);

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

    final public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        $previousChar = $cursor->peek(-1);

        if (null !== $previousChar && !preg_match('!^\s+$!', $previousChar)) {
            return false;
        }

        $previousState = $cursor->saveState();

        if (null === $this->getPrefix()) {
            return false;
        }

        if ($this->getApRegex() && $match = $cursor->match($this->getApRegex())) {
            $name = $match;
        } else {
            $name = $cursor->match($this->getRegex());
        }

        if (null === $name) {
            $cursor->restoreState($previousState);

            return false;
        }

        $link = new Link(
            $this->getUrl($name), $this->getHandle($name), $this->getName($name)
        );

        $inlineContext->getContainer()->appendChild($link);

        return true;
    }

    abstract public function getApRegex(): ?string;

    abstract public function getRegex(): string;

    /**
     * Generates a URL based on the extracted suffix.
     */
    abstract public function getUrl(string $suffix): string;

    private function getHandle(string $suffix): string
    {
        if (2 == substr_count($suffix, '@')) {
            return '@'.explode('@', $suffix)[1];
        }

        return $suffix;
    }

    protected function getName(string $suffix): string
    {
        return $suffix;
    }
}
