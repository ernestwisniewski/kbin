<?php declare(strict_types = 1);

namespace App\Markdown\CommonMark;

use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\ConfigurationInterface;
use function get_class;

final class ExternalImagesRenderer implements InlineRendererInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function render(
        AbstractInline $inline,
        ElementRendererInterface $htmlRenderer
    ): HtmlElement {
        if (!$inline instanceof Image) {
            throw new InvalidArgumentException(
                sprintf(
                    'Incompatible inline type: %s',
                    get_class($inline)
                )
            );
        }

        $url = $title = $inline->getUrl();

        if ($inline->firstChild() instanceof Text) {
            $title = $htmlRenderer->renderInline($inline->firstChild());
        }

        return EmbedElement::buildEmbed($url, $title);
    }

    public function setConfiguration(
        ConfigurationInterface $configuration
    ) {
        $this->config = $configuration;
    }
}
