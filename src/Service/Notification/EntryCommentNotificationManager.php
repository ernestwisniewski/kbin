<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryComment;
use App\Entity\EntryCommentCreatedNotification;
use App\Entity\EntryCommentDeletedNotification;
use App\Entity\EntryCommentEditedNotification;
use App\Entity\EntryCommentReplyNotification;
use App\Entity\Notification;
use App\Entity\User;
use App\Factory\MagazineFactory;
use App\Factory\UserFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

class EntryCommentNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private NotificationRepository $notificationRepository,
        private MagazineSubscriptionRepository $magazineRepository,
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
        private UserFactory $userFactory,
        private PublisherInterface $publisher,
        private Environment $twig,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendCreated(ContentInterface $subject): void
    {
        /**
         * @var EntryComment $subject
         */
        $user = $this->sendUserReplyNotification($subject);
        $this->sendMagazineSubscribersNotification($subject, $user);
    }

    public function sendEdited(ContentInterface $subject): void
    {
        /**
         * @var EntryComment $subject
         */
        $this->notifyMagazine(new EntryCommentEditedNotification($subject->user, $subject));
    }

    private function sendUserReplyNotification(EntryComment $comment): ?User
    {
        if (!$comment->parent || $comment->parent->isAuthor($comment->user)) {
            return null;
        }

        if (!$comment->parent->user->notifyOnNewEntryCommentReply) {
            return null;
        }

        $notification = new EntryCommentReplyNotification($comment->parent->user, $comment);
        $this->notifyUser($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification->user;
    }

    private function notifyUser(EntryCommentReplyNotification $notification): void
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->userFactory->createDto($notification->user));

            $update = new Update(
                $iri,
                $this->getResponse($notification)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function sendMagazineSubscribersNotification(EntryComment $comment, ?User $exclude): void
    {
        $this->notifyMagazine(new EntryCommentCreatedNotification($comment->user, $comment));

        // @todo user followers
        $usersToNotify = [];

        if ($comment->entry->user->notifyOnNewEntryReply && !$comment->isAuthor($comment->entry->user)) {
            $usersToNotify = $this->merge($usersToNotify, [$comment->entry->user]);
        }

        if ($exclude) {
            $usersToNotify = array_filter($usersToNotify, fn($user) => $user !== $exclude);
        }

        foreach ($usersToNotify as $subscriber) {
            $notification = new EntryCommentCreatedNotification($subscriber, $comment);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }

    private function notifyMagazine(Notification $notification): void
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($notification->getComment()->magazine));

            $update = new Update(
                ['pub', $iri],
                $this->getResponse($notification)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function getResponse(Notification $notification): string
    {
        $class = explode("\\", $this->entityManager->getClassMetadata(get_class($notification))->name);

        return json_encode(
            [
                'op' => end($class),
                'id' => $notification->getComment()->getId(),
                'subject' => [
                    'id' => $notification->getComment()->entry->getId(),
                ],
                'toast' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendDeleted(ContentInterface $subject): void
    {
        /**
         * @var EntryComment $subject
         */
        $this->notifyMagazine($notification = new EntryCommentDeletedNotification($subject->user, $subject));
    }

    public function purgeNotifications(EntryComment $comment)
    {
        $notificationsIds = $this->notificationRepository->findEntryCommentNotificationsIds($comment);

        foreach ($notificationsIds as $id) {
            $this->entityManager->remove($this->notificationRepository->find($id));
        }
    }
}
