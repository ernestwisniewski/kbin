<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\EntryComment;
use App\Entity\EntryNotification;
use App\Entity\MagazineSubscription;
use App\Entity\Message;
use App\Entity\MessageNotification;
use App\Entity\MessageThread;
use App\Entity\Notification;
use App\Entity\PostComment;
use App\Entity\PostNotification;
use App\Entity\User;
use App\Repository\EntryNotificationRepository;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Entry;
use App\Entity\Post;

class NotificationManager
{
    private NotificationRepository $notificationRepository;
    private MagazineSubscriptionRepository $magazineSubscriptionRepository;
    private MessageBusInterface $messageBus;
    private EntityManagerInterface $entityManager;

    public function __construct(
        NotificationRepository $notificationRepository,
        MagazineSubscriptionRepository $magazineSubscriptionRepository,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager
    ) {
        $this->notificationRepository         = $notificationRepository;
        $this->magazineSubscriptionRepository = $magazineSubscriptionRepository;
        $this->messageBus                     = $messageBus;
        $this->entityManager                  = $entityManager;
    }

    public function sendNewEntryNotification(Entry $entry): void
    {
        $subs    = $this->getUsersToNotify($this->magazineSubscriptionRepository->findNewEntrySubscribers($entry));
        $follows = [];

        $usersToNotify = $this->merge($subs, $follows);

        foreach ($usersToNotify as $subscriber) {
            $notify = new EntryNotification($subscriber, $entry);
            $this->entityManager->persist($notify);

            // @todo Send push notification to user
        }

        $this->entityManager->flush();
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
            /**
             * @var $notification Notification
             */
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
