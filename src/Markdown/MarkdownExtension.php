<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown;

use App\Markdown\CommonMark\CommunityLinkParser;
use App\Markdown\CommonMark\ExternalImagesRenderer;
use App\Markdown\CommonMark\ExternalLinkRenderer;
use App\Markdown\CommonMark\MentionLinkParser;
use App\Markdown\CommonMark\Node\UnresolvableLink;
use App\Markdown\CommonMark\TagLinkParser;
use App\Markdown\CommonMark\UnresolvableLinkRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

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
        $builder->addSchema('kbin', Expect::structure([
            'render_target' => Expect::type(RenderTarget::class),
        ]));
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
