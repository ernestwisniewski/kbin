<?php declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Service\ActivityPub\ApHttpClient;
use App\Utils\RegPatterns;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TagLinkParser extends AbstractLocalLinkParser
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private ApHttpClient $client)
    {
    }

    public function getPrefix(): string
    {
        return '#';
    }

    public function getRegex(): string
    {
        return '/^#\w{2,25}\b/';
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
