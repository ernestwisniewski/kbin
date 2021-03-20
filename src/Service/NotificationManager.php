<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\EntryComment;
use App\Entity\EntryNotification;
use App\Entity\MagazineSubscription;
use App\Entity\PostComment;
use App\Entity\PostNotification;
use App\Entity\User;
use App\Repository\EntryNotificationRepository;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Entry;
use App\Entity\Post;

class NotificationManager
{
    private MagazineSubscriptionRepository $magazineSubscriptionRepository;
    private MessageBusInterface $messageBus;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MagazineSubscriptionRepository $magazineSubscriptionRepository,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager
    ) {
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

    public function sendPostCommentNotification(PostComment $comment): void
    {

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


}
