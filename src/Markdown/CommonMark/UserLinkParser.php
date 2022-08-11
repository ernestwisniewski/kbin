<?php declare(strict_types = 1);

namespace App\Markdown\CommonMark;

use App\Utils\RegPatterns;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserLinkParser extends AbstractLocalLinkParser
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
        return RegPatterns::LOCAL_USER;
    }

    public function getApRegex(): string
    {
        return RegPatterns::AP_USER;
    }
}
