<?php declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Service\ImageManager;
use League\CommonMark\HtmlElement;

class EmbedElement
{

    public static function buildEmbed(string $url, ?string $label = null): HtmlElement
    {
        $embedClass = ImageManager::isImageUrl($url) ? 'mb-2 ' : 'mb-2 ratio ratio-16x9 ';

        return new HtmlElement(
            'span',
            [
                'data-controller'             => 'embed',
                'data-embed-url-value'        => $url,
                'data-embed-is-visible-value' => 'false',
                'data-embed-hidden-class'     => 'display-none',
                'data-embed-loading-class'    => 'spinner-border',
                'data-embed-embed-class'      => 'fa-photo-video',
                'class'                       => 'me-1 kbin-embed-content',
            ],
            [
                new HtmlElement(
                    'i',
                    [
                        'class'             => 'kbin-preview fas fa-photo-video text-muted me-1',
                        'data-action'       => 'click->embed#fetch',
                        'data-embed-target' => 'embed',
                    ],
                    ''
                ),
                new HtmlElement('a', ['href' => $url, 'rel' => 'nofollow noopener noreferrer', 'target' => '_blank'], $label),
                new HtmlElement(
                    'button', [
                    'class'             => 'btn-close mt-3 ms-1 display-none',
                    'data-embed-target' => 'close',
                    'data-action'       => 'embed#close',
                ], ''
                ),
                new HtmlElement(
                    'span', ['class' => 'kbin-embed d-inline'],
                    new HtmlElement(
                        'span', ['data-embed-target' => 'container', 'class' => $embedClass.'mt-4 display-none kbin-embed-container'], ''
                    ),
                ),
            ]
        );
    }
}
