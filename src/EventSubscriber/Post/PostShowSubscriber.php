<?php declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Entity\Notification;
use App\Entity\Post;
use App\Event\Post\PostHasBeenSeenEvent;
use App\Repository\PostNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class PostShowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private PostNotificationRepository $repository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[ArrayShape([PostHasBeenSeenEvent::class => "string"])] public static function getSubscribedEvents(): array
    {
        return [
            PostHasBeenSeenEvent::class => 'onShowEntry',
        ];
    }

    public function onShowEntry(PostHasBeenSeenEvent $event): void
    {
        $this->readMessage($event->post);
    }

    private function readMessage(Post $post): void
    {
        if (!$this->security->getUser()) {
            return;
        }

        $notification = $this->repository->findNewEntryUnreadNotification($this->security->getUser(), $post);

        if (!$notification) {
            return;
        }

        $notification->status = Notification::STATUS_READ;

        $this->entityManager->flush();
    }
}
