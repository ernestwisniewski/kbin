<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Markdown\CommonMark\Node\MentionLink;
use App\Markdown\CommonMark\Node\TagLink;
use App\Repository\EmbedRepository;
use App\Service\ImageManager;
use App\Service\SettingsManager;
use App\Utils\Embed;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\RegexHelper;

final class ExternalLinkRenderer implements NodeRendererInterface
{
    public function __construct(
        private readonly Embed $embed,
        private readonly EmbedRepository $embedRepository,
        private readonly SettingsManager $settingsManager
    ) {
    }

    /**
     * @param Link $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return HtmlElement
     */
    public function render(
        Node $node,
        ChildNodeRendererInterface $childRenderer
    ): HtmlElement {
        Link::assertInstanceOf($node);

        $url = $title = $node->getUrl();

        if ($node->firstChild() instanceof Text) {
            $title = $childRenderer->renderNodes([$node->firstChild()]);
        }

        $embed = false;
        try {
            if (
                filter_var($url, FILTER_VALIDATE_URL) 
                    && !str_starts_with($title, '@') 
                    && !str_starts_with($title, '#')
            ) {
                if ($entity = $this->embedRepository->findOneBy(['url' => $url])) {
                    $embed = $entity->hasEmbed;
                } else {
                    try {
                        $embed = $this->embed->fetch($url)->html;
                        if ($embed) {
                            $entity = new \App\Entity\Embed($url, (bool)$embed);
                            $this->embedRepository->add($entity);
                        }
                    } catch (\Exception $e) {
                        $embed = false;
                    }

                    if (!$embed) {
                        $entity = new \App\Entity\Embed($url, $embed = false);
                        $this->embedRepository->add($entity);
                    }
                }
            }
        } catch (\Exception $e) {
            $embed = null;
        }

        if (ImageManager::isImageUrl($url) || $embed) {
            return EmbedElement::buildEmbed($url, $title);
        }

        $attr = ['class' => 'kbin-media-link', 'rel' => 'nofollow noopener noreferrer'];

        $attr = match ($node::class) {
            TagLink::class => [
                'class' => 'hashtag tag', 
                'rel'  =>  'tag',
            ],
            MentionLink::class => [
                'class'                    => 'mention u-url',
                'title'                    => $node->data['title'],
                'data-action'              => 'mouseover->kbin#mention',
                'data-kbin-username-param' => $node->data['kbinUsername'],
            ],
            default => [
                'class' => 'kbin-media-link', 
                'rel'   => 'nofollow noopener noreferrer',
            ],
        };

        if (false !== filter_var($url, FILTER_VALIDATE_URL) && !$this->settingsManager->isLocalUrl($url)) {
            $attr['rel'] = 'noopener noreferrer nofollow';
            $attr['target'] = '_blank';
        }

        if (RegexHelper::isLinkPotentiallyUnsafe($url)) {
            return new HtmlElement(
                'span',
                [],
                $title
            );
        }

        return new HtmlElement(
            'a',
            ['href' => $url] + $attr,
            $title
        );
    }
}
