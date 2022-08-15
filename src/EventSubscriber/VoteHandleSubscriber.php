<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\VoteInterface;
use App\Entity\EntryComment;
use App\Entity\PostComment;
use App\Event\VoteEvent;
use App\Message\ActivityPub\Outbox\AnnounceMessage;
use App\Message\Notification\VoteNotificationMessage;
use App\Service\CacheService;
use Doctrine\Common\Util\ClassUtils;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class VoteHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private CacheService $cacheService,
        private CacheInterface $cache
    ) {
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

        if (!$event->vote->user->apId && 1 === $event->vote->choice && !$event->votedAgain) {
            $this->bus->dispatch(
                new AnnounceMessage(
                    $event->vote->user->getId(),
                    $event->votable->getId(),
                    get_class($event->votable),
                    $event->vote->createdAt
                ),
            );
        }
    }

    private function clearCache(VoteInterface $votable)
    {
        $this->cache->delete($this->cacheService->getVotersCacheKey($votable));

        if ($votable instanceof PostComment) {
            $this->cache->invalidateTags(['post_'.$votable->post->getId()]);
        }

        if ($votable instanceof EntryComment && $votable->root) {
            $this->cache->invalidateTags(['entry_comment_'.$votable?->root->getId() ?? $votable->getId()]);
        }
    }
}
