<?php 

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Markdown\CommonMark\Node\TagLink;
use App\Utils\RegPatterns;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TagLinkParser implements InlineParserInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(RegPatterns::LOCAL_TAG_REGEX);
    }

    public function parse(InlineParserContext $ctx): bool
    {
        $cursor = $ctx->getCursor();
        $cursor->advanceBy($ctx->getFullMatchLength());

        [$tag] = $ctx->getSubMatches();

        $url = $this->urlGenerator->generate(
            'tag_overview',
            ['name' => $tag],
            UrlGeneratorInterface::ABSOLUTE_PATH,
        );

        $ctx->getContainer()->appendChild(new TagLink($url, '#' . $tag));

        return true;
    }
}
