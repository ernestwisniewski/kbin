<?php declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\DTO\MagazineDto;
use App\Entity\EntryComment;
use App\Entity\EntryNotification;
use App\Entity\Message;
use App\Entity\MessageNotification;
use App\Entity\PostComment;
use App\Entity\PostNotification;
use App\Entity\User;
use App\Factory\EntryFactory;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Entry;
use App\Entity\Post;

class NotificationManager
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private MagazineSubscriptionRepository $magazineSubscriptionRepository,
        private MessageBusInterface $messageBus,
        private PublisherInterface $publisher,
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendNewEntryNotification(Entry $entry): void
    {
        $subs      = $this->getUsersToNotify($this->magazineSubscriptionRepository->findNewEntrySubscribers($entry));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);

        foreach ($usersToNotify as $subscriber) {
            $notify = new EntryNotification($subscriber, $entry);
            $this->entityManager->persist($notify);
        }

        $this->entityManager->flush();

        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($entry->getMagazine()));

            $update = new Update(
                $iri,
                json_encode(['entryId' => $entry->getId()])
            );

            ($this->publisher)($update);

        } catch (\Exception $e) {

        }
    }

    public function sendEntryCommentNotification(EntryComment $comment): void
    {

    }

    public function sendPostNotification(Post $post): void
    {
        $subs    = $this->getUsersToNotify($this->magazineSubscriptionRepository->findNewPostSubscribers($post));
        $follows = [];

        $usersToNotify = $this->merge($subs, $follows);

        foreach ($usersToNotify as $subscriber) {
            $notify = new PostNotification($subscriber, $post);
            $this->entityManager->persist($notify);

            // @todo Send push notification to user
        }

        $this->entityManager->flush();
    }

    public function sendMessageNotification(Message $message, User $sender): void
    {
        $thread        = $message->getThread();
        $usersToNotify = $thread->getOtherParticipants($sender);

        foreach ($usersToNotify as $subscriber) {
            $notify = new MessageNotification($subscriber, $message);
            $this->entityManager->persist($notify);

            // @todo Send push notification to user
        }

        $this->entityManager->flush();
    }

    public function sendPostCommentNotification(PostComment $comment): void
    {

    }

    public function markAllAsRead(User $user): void
    {
        $notifications = $user->getNewNotifications();

        foreach ($notifications as $notification) {
            $notification->setStatus(Notification::STATUS_READ);
        }

        $this->entityManager->flush();
    }

    public function clear(User $user): void
    {
        $notifications = $user->getNotifications();

        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();
    }

    private function getUsersToNotify(array $subscriptions): array
    {
        return array_map(
            function ($sub) {
                $sub->getUser()->getId();

                return $sub->getUser();
            },
            $subscriptions
        );
    }

    private function merge(array $subs, array $follows): array
    {
        return array_merge(
            $subs,
            array_filter(
                $follows,
                function ($val) use ($subs) {
                    return !in_array($val, $subs);
                }
            )
        );
    }

    public function readMessageNotification(Message $message, User $user): void
    {
        $repo = $this->entityManager->getRepository(MessageNotification::class);

        $notifications = $repo->findBy(
            [
                'message' => $message,
                'user'    => $user,
            ]
        );

        foreach ($notifications as $notification) {
            $notification->setStatus(Notification::STATUS_READ);
        }

        $this->entityManager->flush();
    }
}
