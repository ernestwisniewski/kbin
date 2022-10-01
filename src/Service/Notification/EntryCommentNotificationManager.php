<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryComment;
use App\Entity\EntryCommentCreatedNotification;
use App\Entity\EntryCommentDeletedNotification;
use App\Entity\EntryCommentEditedNotification;
use App\Entity\EntryCommentMentionedNotification;
use App\Entity\EntryCommentReplyNotification;
use App\Entity\Notification;
use App\Factory\MagazineFactory;
use App\Factory\UserFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use App\Service\MentionManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class EntryCommentNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private MentionManager $mentionManager,
        private NotificationRepository $notificationRepository,
        private MagazineSubscriptionRepository $magazineRepository,
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
        private UserFactory $userFactory,
        private HubInterface $publisher,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager
    ) {
    }

    // @todo check if author is on the block list
    public function sendCreated(ContentInterface $subject): void
    {
        /**
         * @var EntryComment $subject
         */
        $users = $this->sendMentionedNotification($subject);
        $users = $this->sendUserReplyNotification($subject, $users);
        $this->sendMagazineSubscribersNotification($subject, $users);
    }

    public function sendEdited(ContentInterface $subject): void
    {
        /**
         * @var EntryComment $subject
         */
        $this->notifyMagazine(new EntryCommentEditedNotification($subject->user, $subject));
    }

    private function sendMentionedNotification(EntryComment $subject): array
    {
        $users = [];
        foreach ($this->mentionManager->getUsersFromArray($subject->mentions) as $user) {
            if (!$user->apId) {
                $notification = new EntryCommentMentionedNotification($user, $subject);
                $this->entityManager->persist($notification);
            }

            $users[] = $user;
        }

        return $users;
    }

    private function sendUserReplyNotification(EntryComment $comment, array $exclude): array
    {
        if (!$comment->parent || $comment->parent->isAuthor($comment->user)) {
            return $exclude;
        }

        if (!$comment->parent->user->notifyOnNewEntryCommentReply) {
            return $exclude;
        }

        if (in_array($comment->parent->user, $exclude)) {
            return $exclude;
        }

        if ($comment->parent->user->apId) {
            // @todo activtypub
            $exclude[] = $comment->parent->user;

            return $exclude;
        }

        $notification = new EntryCommentReplyNotification($comment->parent->user, $comment);
        $this->notifyUser($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $exclude[] = $notification->user;

        return $exclude;
    }

    private function notifyUser(EntryCommentReplyNotification $notification): void
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->userFactory->createDto($notification->user));

            $update = new Update(
                $iri,
                $this->getResponse($notification)
            );

            $this->publisher->publish($update);

        } catch (Exception $e) {
        }
    }

    private function sendMagazineSubscribersNotification(EntryComment $comment, array $exclude): void
    {
        $this->notifyMagazine(new EntryCommentCreatedNotification($comment->user, $comment));

        $usersToNotify = []; // @todo user followers
        if ($comment->entry->user->notifyOnNewEntryReply && !$comment->isAuthor($comment->entry->user)) {
            $usersToNotify = $this->merge(
                $usersToNotify,
                [$comment->entry->user]
            );
        }

        if (count($exclude)) {
            $usersToNotify = array_filter($usersToNotify, fn($user) => !in_array($user, $exclude));
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

            $this->publisher->publish($update);

        } catch (Exception $e) {
        }
    }


    private function getResponse(Notification $notification): string
    {
        $class = explode("\\", $this->entityManager->getClassMetadata(get_class($notification))->name);

        /**
         * @var EntryComment $comment ;
         */
        $comment = $notification->getComment();

        return json_encode(
            [
                'op' => end($class),
                'id' => $comment->getId(),
                'subject' => [
                    'id' => $comment->entry->getId(),
                ],
                'title' => $comment->entry->title,
                'body' => $comment->body,
                'icon' => null,
                'url' => $this->urlGenerator->generate('entry_single', [
                    'magazine_name' => $comment->magazine->name,
                    'entry_id'      => $comment->entry->getId(),
                    'slug'          => $comment->entry->slug,
                ]),
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
