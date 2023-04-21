<?php

declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Entity\Post;
use App\Event\Post\PostCreatedEvent;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\Notification\PostCreatedNotificationMessage;
use App\Repository\MagazineRepository;
use App\Service\PostManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly MagazineRepository $magazineRepository,
        private readonly PostManager $postManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCreatedEvent::class => 'onPostCreated',
        ];
    }

    public function onPostCreated(PostCreatedEvent $event): void
    {
        if (!$event->post->apId) {
            $this->bus->dispatch(new CreateMessage($event->post->getId(), get_class($event->post)));
        } else {
            $this->handleMagazine($event->post);
        }

        $this->bus->dispatch(new PostCreatedNotificationMessage($event->post->getId()));
    }

    private function handleMagazine(Post $post): void
    {
        if (!$post->tags) {
            return;
        }

        foreach ($post->tags as $tag) {
            if ($magazine = $this->magazineRepository->findOneByName($tag)) {
                $this->postManager->changeMagazine($post, $magazine);
                break;
            }

            if ($magazine = $this->magazineRepository->findByTag($tag)) {
                $this->postManager->changeMagazine($post, $magazine);
                break;
            }
        }
    }
}
