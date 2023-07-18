<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Service\DomainManager;
use League\CommonMark\Util\HtmlElement;

class EmbedElement
{
    public static function buildEmbed(string $url, string $label = null): HtmlElement
    {
        return new HtmlElement(
            'span',
            [
                'class' => 'preview',
                'data-controller' => 'preview',
            ],
            [
                new HtmlElement(
                    'button',
                    [
                        'class' => 'show-preview',
                        'data-action' => 'preview#show',
                        'data-preview-url-param' => $url,
                        'data-preview-ratio-param' => DomainManager::shouldRatio($url) ? '1' : '0',
                        'aria-label' => 'Show preview',
                    ],
                    new HtmlElement(
                        'i',
                        [
                            'class' => 'fas fa-photo-video',
                        ],
                        ''
                    ),
                ),
                new HtmlElement(
                    'a',
                    ['href' => $url, 'rel' => 'nofollow noopener noreferrer', 'target' => '_blank'],
                    $label
                ),
            ]
        );
    }
}
