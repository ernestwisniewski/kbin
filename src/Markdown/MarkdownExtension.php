<?php

declare(strict_types=1);

namespace App\Markdown;

use App\Markdown\CommonMark\{
    CommunityLinkParser,
    ExternalImagesRenderer,
    ExternalLinkRenderer,
    MentionLinkParser,
    TagLinkParser,
    UnresolvableLinkRenderer,
};
use App\Markdown\CommonMark\Node\UnresolvableLink;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;

final class MarkdownExtension implements ConfigurableExtensionInterface
{
    public function __construct(
        private readonly CommunityLinkParser $communityLinkParser,
        private readonly MentionLinkParser $mentionLinkParser,
        private readonly TagLinkParser $tagLinkParser,
        private readonly ExternalLinkRenderer $linkRenderer,
        private readonly ExternalImagesRenderer $imagesRenderer,
        private readonly UnresolvableLinkRenderer $unresolvableLinkRenderer,
    ) {
    }

    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->merge([
            'renderer' => [
                'soft_break' => "<br>\r\n",
            ],
            'html_input' => 'escape',
            'allow_unsafe_links' => false
        ]);
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser($this->communityLinkParser);
        $environment->addInlineParser($this->mentionLinkParser);
        $environment->addInlineParser($this->tagLinkParser);
        
        $environment->addRenderer(Link::class, $this->linkRenderer, 1);
        $environment->addRenderer(Image::class, $this->imagesRenderer, 1);
        $environment->addRenderer(UnresolvableLink::class, $this->unresolvableLinkRenderer, 1);
    }
}