<?php

namespace App\Markdown\CommonMark;

use App\Utils\Embed;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\ConfigurationInterface;
use League\CommonMark\Util\RegexHelper;

final class ExternalLinkRenderer implements InlineRendererInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;
    private Embed $embed;

    public function __construct(Embed $embed)
    {
        $this->embed = $embed;
    }

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

        $url = $title = $inline->getUrl();

        if ($inline->firstChild() instanceof Text) {
            $title = $htmlRenderer->renderInline($inline->firstChild());
        }

        try {
            $embed = $this->embed->fetch($url)->getHtml();
        } catch (\Exception $e) {
            $embed = null;
        }

        try {
            $isImage = getimagesize($url);
        } catch (\Exception $e) {
            $isImage = false;
        }

        if ($isImage || $embed) {
            return EmbedElement::buildEmbed($url, $title);
        }

        return new HtmlElement('a', ['href' => $url] + ['class' => 'kbin-media-link'], $title);
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
