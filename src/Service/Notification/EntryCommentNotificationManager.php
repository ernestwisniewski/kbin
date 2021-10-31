<?php declare(strict_types = 1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryComment;
use App\Entity\EntryCommentCreatedNotification;
use App\Entity\EntryCommentDeletedNotification;
use App\Entity\EntryCommentReplyNotification;
use App\Entity\User;
use App\Factory\MagazineFactory;
use App\Factory\UserFactory;
use App\Repository\MagazineSubscriptionRepository;
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
        private MagazineSubscriptionRepository $repository,
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
                $this->getReplyResponse($notification)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function getReplyResponse(EntryCommentReplyNotification $notification): string
    {
        return json_encode(
            [
                'op'    => 'EntryCommentReplyNotification',
                'id'    => $notification->getComment()->getId(),
                'data'  => [],
                'toast' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
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

    private function notifyMagazine(EntryCommentCreatedNotification $notification): void
    {

        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($notification->getComment()->magazine));

            $update = new Update(
                ['pub', $iri],
                $this->getCreatedResponse($notification)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function getCreatedResponse(EntryCommentCreatedNotification $notification): string
    {
        return json_encode(
            [
                'op'      => 'EntryCommentCreatedNotification',
                'id'      => $notification->getComment()->getId(),
                'subject' => [
                    'id' => $notification->getComment()->entry->getId(),
                ],
                'toast'   => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendDeleted(ContentInterface $subject): void
    {
        /**
         * @var EntryComment $subject
         */
        $notification = new EntryCommentDeletedNotification($subject->getUser(), $subject);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
