<?php declare(strict_types = 1);

namespace App\Markdown\CommonMark;

use App\Service\ImageManager;
use League\CommonMark\HtmlElement;

class EmbedElement
{

    public static function buildEmbed(string $url, ?string $label = null): HtmlElement
    {
        $embedClass = ImageManager::isImageUrl($url) ? '' : 'ratio ratio-16x9 ';

        return new HtmlElement(
            'div',
            [
                'data-controller'             => 'embed',
                'data-embed-url-value'        => $url,
                'data-embed-is-visible-value' => 'false',
                'data-embed-hidden-class'     => 'display-none',
                'data-embed-loading-class'    => 'spinner-border',
                'data-embed-embed-class'      => 'fa-photo-video',
            ],
            [
                new HtmlElement(
                    'i',
                    [
                        'class'             => 'kbin-preview fas fa-photo-video text-muted me-1 float-start',
                        'data-action'       => 'click->embed#fetch',
                        'data-embed-target' => 'embed',
                    ],
                    ''
                ),
                new HtmlElement('a', ['href' => $url], $label),
                new HtmlElement('span', ['class' => 'clearfix'], ''),
                new HtmlElement(
                    'button', [
                    'class'             => 'btn-close mt-3 display-none',
                    'data-embed-target' => 'close',
                    'data-action'       => 'embed#close',
                ], ''
                ),
                new HtmlElement(
                    'div', ['class' => 'kbin-embed'],
                    new HtmlElement('div', ['data-embed-target' => 'container', 'class' => $embedClass.'mt-4 display-none'], ''),
                ),
            ]
        );
    }
}
