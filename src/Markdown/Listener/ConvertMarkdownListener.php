<?php declare(strict_types = 1);

namespace App\Markdown\Listener;

use App\Markdown\Event\ConfigureCommonMark;
use App\Markdown\Event\ConvertMarkdown;
use App\Markdown\Factory\ConverterFactory;
use App\Markdown\Factory\EnvironmentFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConvertMarkdownListener implements EventSubscriberInterface
{
    public function __construct(
        private ConverterFactory $converterFactory,
        private EnvironmentFactory $environmentFactory,
        private EventDispatcherInterface $dispatcher
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
        $environment = $this->environmentFactory->createConfigurableEnvironment();

        $configureEvent = new ConfigureCommonMark($environment, $event);
        $this->dispatcher->dispatch($configureEvent);

        $converter = $this->converterFactory->createConverter($environment);
        $html      = $converter->convertToHtml($event->getMarkdown());

        $event->setRenderedHtml($html);
    }
}
