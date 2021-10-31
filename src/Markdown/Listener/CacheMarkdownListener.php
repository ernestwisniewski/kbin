<?php declare(strict_types = 1);

namespace App\Markdown\Listener;

use App\Markdown\Event\BuildCacheContext;
use App\Markdown\Event\ConvertMarkdown;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function assert;

/**
 * Fetch and store rendered HTML given the raw input and a generated context.
 */
final class CacheMarkdownListener implements EventSubscriberInterface
{
    private const ATTR_CACHE_ITEM = __CLASS__.' cache item';
    public const ATTR_NO_CACHE_STORE = 'no_cache_store';

    public function __construct(
        private CacheItemPoolInterface $markdownCache,
        private EventDispatcherInterface $dispatcher
    ) {
        $this->pool       = $markdownCache;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConvertMarkdown::class => [
                ['preConvertMarkdown', 64],
                ['postConvertMarkdown', -64],
            ],
        ];
    }

    public function preConvertMarkdown(ConvertMarkdown $event): void
    {
        $cacheEvent = new BuildCacheContext($event);
        $this->dispatcher->dispatch($cacheEvent);

        $item = $this->pool->getItem($cacheEvent->getCacheKey());

        if ($item->isHit()) {
            $event->setRenderedHtml($item->get());
            $event->stopPropagation();
        } elseif (!$event->getAttribute(self::ATTR_NO_CACHE_STORE)) {
            $event->addAttribute(self::ATTR_CACHE_ITEM, $item);
        }
    }

    public function postConvertMarkdown(ConvertMarkdown $event): void
    {
        if ($event->getAttribute(self::ATTR_NO_CACHE_STORE)) {
            return;
        }

        $item = $event->getAttribute(self::ATTR_CACHE_ITEM);
        assert($item instanceof CacheItemInterface);

        $item->set($event->getRenderedHtml());
        $this->pool->save($item);

        $event->removeAttribute(self::ATTR_CACHE_ITEM);
    }
}
