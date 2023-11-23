<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown\Listener;

use App\Markdown\Event\ConvertMarkdown;
use League\CommonMark\Output\RenderedContent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SpoilerMarkdownListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConvertMarkdown::class => [
                ['postConvertMarkdown', -60],
            ],
        ];
    }

    public function postConvertMarkdown(ConvertMarkdown $event): void
    {
        $html = $event->getRenderedContent();
        $content = $html->getContent();

        $content = strip_tags($content, ['<p>', '<br>', '<a>', '<strong>', '<i>', '<img>']);
        $regexp = '/(?<!\S)(?:::|<p>:::)\s+spoiler\s+(?<title>.+)\n(?<body>.(?:.*\n)+?):::(<br\/>|<\/p>)?\n/m';

        preg_match_all($regexp, $content, $matches, PREG_SET_ORDER);

        if (!$matches) {
            return;
        }

        foreach ($matches as $match) {
            $content = str_replace(
                $match[0],
                '<details><summary>'.$match['title'].'</summary>'.$match['body'].'</details>',
                $content
            );
        }

        $rendered = new RenderedContent($html->getDocument(), $content);

        $event->setRenderedContent($rendered);
    }
}
