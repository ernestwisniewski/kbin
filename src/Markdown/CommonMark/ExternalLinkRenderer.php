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

final class ExternalLinkRenderer implements InlineRendererInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function render(
        AbstractInline $inline,
        ElementRendererInterface $htmlRenderer
    ): HtmlElement {
        if (!$inline instanceof Link) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incompatible inline type: %s',
                    \get_class($inline)
                )
            );
        }

        $url          = $inline->getUrl();
        $attr['href'] = $url;

       if (getimagesize($url)) {
            return new HtmlElement(
                'span',
                [],
                [
                    new HtmlElement('i', ['class' => 'kbin-preview fas fa-photo-video text-muted me-1 float-start'], ''),
                    new HtmlElement('a', $attr, $url),
                ]
            );
        }

        return new HtmlElement('a', $attr + ['class' => 'kbin-media-link'], $url);
    }

    public function setConfiguration(
        ConfigurationInterface $configuration
    ) {
        $this->config = $configuration;
    }

    private function isImage()
    {
    }
}
