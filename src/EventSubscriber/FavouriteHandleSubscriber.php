<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Event\FavouriteEvent;
use App\Kbin\Vote\VoteRemove;
use App\Message\ActivityPub\Outbox\LikeMessage;
use App\Message\Notification\FavouriteNotificationMessage;
use App\Service\CacheService;
use Doctrine\Common\Util\ClassUtils;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class FavouriteHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly VoteRemove $voteRemove,
        private readonly MessageBusInterface $bus,
        private readonly CacheInterface $cache,
        private readonly CacheService $cacheService,
    ) {
    }

    #[ArrayShape([FavouriteEvent::class => 'string'])]
    public static function getSubscribedEvents(): array
    {
        return [
            FavouriteEvent::class => 'onFavourite',
        ];
    }

    public function onFavourite(FavouriteEvent $event): void
    {
        $subject = $event->subject;
        $choice = $event->subject->getUserVote($event->user)?->choice;
        if (VotableInterface::VOTE_DOWN === $choice && $subject->isFavored($event->user)) {
            ($this->voteRemove)($subject, $event->user);
        }

        $this->bus->dispatch(
            new FavouriteNotificationMessage(
                $subject->getId(),
                ClassUtils::getRealClass(\get_class($event->subject))
            )
        );

        $this->deleteFavouriteCache($subject);

        match (\get_class($subject)) {
            EntryComment::class => $this->clearEntryCommentCache($subject),
            PostComment::class => $this->clearPostCommentCache($subject),
            Entry::class => $this->clearEntryCache($subject),
            Post::class => $this->clearPostCache($subject),
            default => null
        };

        if (!$event->user->apId) {
            $this->bus->dispatch(
                new LikeMessage(
                    $event->user->getId(),
                    $subject->getId(),
                    \get_class($subject),
                    $event->removeLike
                ),
            );
        }
    }

    private function deleteFavouriteCache(FavouriteInterface $subject): void
    {
        $this->cache->delete($this->cacheService->getFavouritesCacheKey($subject));
    }

    private function clearEntryCommentCache(EntryComment $comment): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$comment->root?->getId() ?? $comment->getId()]);
    }

    private function clearPostCommentCache(PostComment $comment): void
    {
        $this->cache->invalidateTags([
            'post_'.$comment->post->getId(),
            'post_comment_'.$comment->root?->getId() ?? $comment->getId(),
        ]);
    }

    private function clearEntryCache(Entry $entry): void
    {
        $this->cache->invalidateTags([
            'entry_'.$entry->getId(),
        ]);
    }

    private function clearPostCache(Post $post): void
    {
        $this->cache->invalidateTags([
            'post_'.$post->getId(),
        ]);
    }
}
