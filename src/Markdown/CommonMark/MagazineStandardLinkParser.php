<?php declare(strict_types = 1);

namespace App\Markdown\CommonMark;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MagazineStandardLinkParser extends AbstractLocalLinkParser
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getPrefix(): string
    {
        return '!';
    }

    protected function kbinPrefix(): bool
    {
        return false;
    }

    public function getUrl(string $suffix): string
    {
        return $this->urlGenerator->generate(
            'front_magazine',
            [
                'name' => $suffix,
            ]
        );
    }

    public function getRegex(): string
    {
        return '/^!\w{2,25}\b/';
    }
}
