<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class ExternalImagesRenderer implements NodeRendererInterface
{
    /**
     * @param Image $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return HtmlElement
     */
    public function render(
        Node $node,
        ChildNodeRendererInterface $childRenderer
    ): HtmlElement {
        Image::assertInstanceOf($node);

        $url = $title = $node->getUrl();

        if ($node->firstChild() instanceof Text) {
            $title = $childRenderer->renderNodes([$node->firstChild()]);
        }

        return EmbedElement::buildEmbed($url, $title);
    }
}
