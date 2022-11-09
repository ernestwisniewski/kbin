<?php declare(strict_types=1);

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
        private MessageBusInterface $bus,
        private MagazineRepository $magazineRepository,
        private PostManager $postManager
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
        $this->bus->dispatch(new PostCreatedNotificationMessage($event->post->getId()));

        if (!$event->post->apId) {
            $this->bus->dispatch(new CreateMessage($event->post->getId(), get_class($event->post)));
        } else {
            $this->handleMagazine($event->post);
        }
    }

    private function handleMagazine(Post $post): void
    {
        if (!$post->tags) {
            return;
        }

        foreach ($post->tags as $tag) {
            if ($magazines = $this->magazineRepository->findByTag($tag)) {
                $this->postManager->changeMagazine($post, $magazines[array_rand($magazines)]);
                break;
            }
        }
    }
}
