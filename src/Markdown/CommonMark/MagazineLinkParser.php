<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Markdown\CommonMark\Node\TagLink;
use App\Repository\MagazineRepository;
use App\Service\SettingsManager;
use App\Utils\RegPatterns;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MagazineLinkParser implements InlineParserInterface
{
    public function __construct(
        private readonly MagazineRepository $magazineRepository,
        private readonly SettingsManager $settingsManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex('\B!(\w{1,30})(?:@)?((?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++|[a-z0-9\-\_]++)?');
    }

    public function parse(InlineParserContext $ctx): bool
    {
        $cursor = $ctx->getCursor();
        $cursor->advanceBy($ctx->getFullMatchLength());

        $matches = $ctx->getSubMatches();
        $name    = $matches['0'];
        $domain  = $matches['1'] ?? null;

        if ($domain !== $this->settingsManager->get('KBIN_DOMAIN')) {
            $domain = null;
        }       
        
        $url = $this->urlGenerator->generate(
            'front_magazine',
            ['name' => $tag],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        
        $ctx->getContainer()->appendChild(new MentionLink($url, '@' . $username));

        return true;
    }
}
