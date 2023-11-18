<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown\Listener;

use App\Markdown\Event\ConvertMarkdown;
use App\Markdown\Factory\ConverterFactory;
use App\Markdown\Factory\EnvironmentFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConvertMarkdownListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ConverterFactory $converterFactory,
        private readonly EnvironmentFactory $environmentFactory,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConvertMarkdown::class => ['onConvertMarkdown'],
        ];
    }

    public function onConvertMarkdown(ConvertMarkdown $event): void
    {
        $environment = $this->environmentFactory->createEnvironment($event->getRenderTarget());

        $converter = $this->converterFactory->createConverter($environment);
        $html = $converter->convert($event->getMarkdown());

        $event->setRenderedContent($html);
    }
}
