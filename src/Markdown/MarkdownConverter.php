<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown;

use App\Markdown\Event\ConvertMarkdown;
use Psr\EventDispatcher\EventDispatcherInterface;

class MarkdownConverter
{
    public const RENDER_TARGET = 'render_target';

    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function convertToHtml(string $markdown, array $context = []): string
    {
        $event = new ConvertMarkdown($markdown);
        $event->mergeAttributes($context);

        $this->dispatcher->dispatch($event);

        return (string) $event->getRenderedContent();
    }
}
