<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

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
