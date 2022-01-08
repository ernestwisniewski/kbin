<?php declare(strict_types = 1);

namespace App\Markdown\CommonMark;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserStandardLinkParser extends AbstractLocalLinkParser
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getPrefix(): string
    {
        return '@';
    }

    protected function kbinPrefix(): bool
    {
        return false;
    }

    public function getUrl(string $suffix): string
    {
        return $this->urlGenerator->generate(
            'user',
            [
                'username' => $suffix,
            ]
        );
    }

    public function getRegex(): string
    {
        return '/^@\w{2,35}\b/';
    }
}
