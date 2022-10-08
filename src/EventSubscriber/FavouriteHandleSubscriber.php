<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\EntryComment;
use App\Entity\PostComment;
use App\Event\FavouriteEvent;
use App\Message\ActivityPub\Outbox\LikeMessage;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class FavouriteHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private CacheInterface $cache
    ) {
    }

    #[ArrayShape([FavouriteEvent::class => "string"])] public static function getSubscribedEvents(): array
    {
        return [
            FavouriteEvent::class => 'onFavourite',
        ];
    }

    public function onFavourite(FavouriteEvent $event): void
    {
        $subject = $event->subject;

        match (get_class($subject)) {
            EntryComment::class => $this->clearEntryCommentCache($subject),
            PostComment::class => $this->clearPostCommentCache($subject),
            default => null
        };

        if (!$event->user->apId) {
            $this->bus->dispatch(
                new LikeMessage(
                    $event->user->getId(),
                    $subject->getId(),
                    get_class($subject),
                    $event->removeLike
                ),
            );
        }
    }


    private function clearEntryCommentCache(EntryComment $comment): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$comment->root?->getId() ?? $comment->getId()]);
    }

    private function clearPostCommentCache(PostComment $comment)
    {
        $this->cache->invalidateTags(['post_'.$comment->post->getId()]);
    }
}
