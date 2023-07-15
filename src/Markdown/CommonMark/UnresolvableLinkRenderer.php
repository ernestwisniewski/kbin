<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Markdown\CommonMark\Node\UnresolvableLink;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class UnresolvableLinkRenderer implements NodeRendererInterface
{
    /**
     * @param UnresolvableLink $node
     */
    public function render(
        Node $node,
        ChildNodeRendererInterface $childRenderer
    ): HtmlElement {
        UnresolvableLink::assertInstanceOf($node);

        return new HtmlElement(
            'span',
            [
                'class' => 'mention mention--unresolvable',
            ],
            $node->getLiteral(),
        );
    }
}
