<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Markdown\CommonMark\Node\ActivityPubMentionLink;
use App\Markdown\CommonMark\Node\ActorSearchLink;
use App\Markdown\CommonMark\Node\CommunityLink;
use App\Markdown\CommonMark\Node\MentionLink;
use App\Markdown\CommonMark\Node\RoutedMentionLink;
use App\Markdown\CommonMark\Node\TagLink;
use App\Markdown\MarkdownConverter;
use App\Markdown\RenderTarget;
use App\Repository\EmbedRepository;
use App\Service\ImageManager;
use App\Service\SettingsManager;
use App\Utils\Embed;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\RegexHelper;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ExternalLinkRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    private ConfigurationInterface $config;

    public function __construct(
        private readonly Embed $embed,
        private readonly EmbedRepository $embedRepository,
        private readonly SettingsManager $settingsManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function render(
        Node $node,
        ChildNodeRendererInterface $childRenderer
    ): HtmlElement {
        /* @var Link $node */
        Link::assertInstanceOf($node);

        $renderTarget = $this->config->get('kbin')[MarkdownConverter::RENDER_TARGET];

        $url = $title = match ($node::class) {
            RoutedMentionLink::class => $this->generateUrlForRoute($node, $renderTarget),
            default => $node->getUrl()
        };

        if (RegexHelper::isLinkPotentiallyUnsafe($url)) {
            return new HtmlElement(
                'span',
                ['class' => 'unsafe-link'],
                $title
            );
        }

        if ($node->firstChild() instanceof Text) {
            $title = $childRenderer->renderNodes([$node->firstChild()]);
        }

        if (
            !$this->isMentionType($node)
                && (ImageManager::isImageUrl($url)
                    || $this->isEmbed($url, $title)
                )
        ) {
            return EmbedElement::buildEmbed($url, $title);
        }

        // create attributes for link
        $attr = $this->generateAttr($node, $renderTarget);

        // open non-local links in a new tab
        if (false !== filter_var($url, FILTER_VALIDATE_URL)
            && !$this->settingsManager->isLocalUrl($url)
            && RenderTarget::ActivityPub !== $renderTarget
        ) {
            $attr['rel'] = 'noopener noreferrer nofollow';
            $attr['target'] = '_blank';
        }

        return new HtmlElement(
            'a',
            ['href' => $url] + $attr,
            $title
        );
    }

    /**
     * @return array{
     *     class: string,
     *     title?: string,
     *     data-action?: string,
     *     data-mentions-username-param?: string,
     *     rel?: string,
     * }
     */
    private function generateAttr(Link $node, RenderTarget $renderTarget): array
    {
        $attr = match ($node::class) {
            ActivityPubMentionLink::class => $this->generateMentionLinkAttr($node),
            ActorSearchLink::class => [],
            CommunityLink::class => $this->generateCommunityLinkAttr($node),
            RoutedMentionLink::class => $this->generateMentionLinkAttr($node),
            TagLink::class => [
                'class' => 'hashtag tag',
                'rel' => 'tag',
            ],
            default => [
                'class' => 'kbin-media-link',
            ],
        };

        if (RenderTarget::ActivityPub === $renderTarget) {
            $attr = array_intersect_key(
                $attr,
                array_flip([
                    'class',
                    'title',
                    'rel',
                ])
            );
        }

        return $attr;
    }

    /**
     * @return array{
     *     class: string,
     *     title: string,
     *     data-action: string,
     *     data-mentions-username-param: string,
     * }
     */
    private function generateMentionLinkAttr(MentionLink $link): array
    {
        $data = [
            'class' => 'mention',
            'title' => $link->getTitle(),
            'data-mentions-username-param' => $link->getKbinUsername(),
        ];

        if (MentionType::Magazine === $link->getType() || MentionType::RemoteMagazine === $link->getType()) {
            $data['class'] = $data['class'].' mention--magazine';
            $data['data-action'] = 'mentions#navigate_magazine';
        }

        if (MentionType::User === $link->getType() || MentionType::RemoteUser === $link->getType()) {
            $data['class'] = $data['class'].' u-url mention--user';
            $data['data-action'] = 'mouseover->mentions#user_popup mentions#navigate_user';
        }

        return $data;
    }

    /**
     * @return array{
     *     class: string,
     *     title: string,
     *     data-action: string,
     *     data-mentions-username-param: string,
     * }
     */
    private function generateCommunityLinkAttr(CommunityLink $link): array
    {
        $data = [
            'class' => 'mention mention--magazine',
            'title' => $link->getTitle(),
            'data-mentions-username-param' => $link->getKbinUsername(),
            'data-action' => 'mentions#navigate_magazine',
        ];

        return $data;
    }

    private function generateUrlForRoute(RoutedMentionLink $routedMentionLink, RenderTarget $renderTarget): string
    {
        return $this->urlGenerator->generate(
            $routedMentionLink->getRoute(),
            [$routedMentionLink->getParamName() => $routedMentionLink->getUrl()],
            RenderTarget::ActivityPub === $renderTarget
                ? UrlGeneratorInterface::ABSOLUTE_URL
                : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    private function isEmbed(string $url, string $title): bool
    {
        $embed = false;
        if (filter_var($url, FILTER_VALIDATE_URL) && $entity = $this->embedRepository->findOneBy(['url' => $url])) {
            $embed = $entity->hasEmbed;
        }

        return (bool) $embed;
    }

    private function isMentionType(Link $link): bool
    {
        $types = [
            ActivityPubMentionLink::class,
            ActorSearchLink::class,
            CommunityLink::class,
            RoutedMentionLink::class,
            TagLink::class,
        ];

        foreach ($types as $type) {
            if ($link instanceof $type) {
                return true;
            }
        }

        return false;
    }
}
