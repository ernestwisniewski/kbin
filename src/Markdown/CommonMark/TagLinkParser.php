<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TagLinkParser extends AbstractLocalLinkParser
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getPrefix(): string
    {
        return '#';
    }

    public function getRegex(): string
    {
        return '/^#[a-zA-ZżźćńółęąśŻŹĆĄŚĘŁÓŃ0-9_]{2,35}/';
    }

    public function getUrl(string $suffix): string
    {
        return $this->urlGenerator->generate(
            'tag_overall',
            ['name' => str_replace('#', '', $suffix)],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function getApRegex(): ?string
    {
        return null;
    }
}
