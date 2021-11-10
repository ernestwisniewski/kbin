<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\VoteInterface;
use App\Event\VoteEvent;
use App\Message\Notification\VoteNotificationMessage;
use App\Service\CacheService;
use Doctrine\Common\Util\ClassUtils;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class VoteHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus, private CacheService $cacheService, private CacheInterface $cache)
    {
    }

    #[ArrayShape([VoteEvent::class => "string"])] public static function getSubscribedEvents(): array
    {
        return [
            VoteEvent::class => 'onVote',
        ];
    }

    public function onVote(VoteEvent $event): void
    {
        $this->clearCache($event->votable);

        $this->bus->dispatch(
            (
            new VoteNotificationMessage(
                $event->votable->getId(),
                ClassUtils::getRealClass(get_class($event->votable))
            ))
        );
    }

    private function clearCache(VoteInterface $votable)
    {
        $this->cache->delete($this->cacheService->getVotersCacheKey($votable));
    }
}
