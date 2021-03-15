<?php

namespace App\Markdown\CommonMark;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\ConfigurationInterface;
use League\CommonMark\Util\RegexHelper;

final class ExternalImagesRenderer implements InlineRendererInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function render(
        AbstractInline $inline,
        ElementRendererInterface $htmlRenderer
    ): HtmlElement {
        if (!$inline instanceof Image) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incompatible inline type: %s',
                    \get_class($inline)
                )
            );
        }

        $url = $inline->getUrl();

        $attr = [
            'class' => 'kbin-media-link',
            'href'  => $url,
        ];

        return new HtmlElement(
            'span',
            [],
            [
                new HtmlElement('i', ['class' => 'kbin-preview fas fa-photo-video text-muted me-1'], ''),
                new HtmlElement('a', $attr, $url)
            ]
        );
    }

    public function setConfiguration(
        ConfigurationInterface $configuration
    ) {
        $this->config = $configuration;
    }
}
