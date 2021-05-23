<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\EntryComment;
use App\Entity\EntryCommentCreatedNotification;
use App\Entity\EntryCommentDeletedNotification;
use App\Entity\EntryCommentReplyNotification;
use App\Factory\MagazineFactory;
use App\Factory\UserFactory;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;
use function count;

class EntryCommentNotificationManager
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

    public function sendCreated(EntryComment $comment): void
    {
        $this->sendMagazineSubscribersNotification($comment);
        $this->sendUserReplyNotification($comment);
    }

    private function sendMagazineSubscribersNotification(EntryComment $comment): void
    {
        $this->notifyMagazine(new EntryCommentCreatedNotification($comment->user, $comment));

        $subs      = $this->getUsersToNotify($this->repository->findNewEntrySubscribers($comment->entry));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);

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
                $iri,
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
                'op'   => 'EntryCommentNotification',
                'id'   => $notification->getComment()->getId(),
                'data' => [],
                'html' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    private function sendUserReplyNotification(EntryComment $comment):void
    {
        if(!$comment->parent || $comment->parent->isAuthor($comment->user)) {
            return;
        }

        $notification = new EntryCommentReplyNotification($comment->parent->user, $comment);
        $this->notifyUser($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
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
                'op'   => 'EntryCommentReplyNotification',
                'id'   => $notification->getComment()->getId(),
                'data' => [],
                'html' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendDeleted(EntryComment $comment): void
    {
        $notification = new EntryCommentDeletedNotification($comment->getUser(), $comment);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
