<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\EntryComment;
use App\Entity\EntryCommentCreatedNotification;
use App\Entity\EntryCommentDeletedNotification;
use App\Factory\MagazineFactory;
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
        private PublisherInterface $publisher,
        private Environment $twig,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendCreated(EntryComment $comment): void
    {
        $subs      = $this->getUsersToNotify($this->repository->findNewEntrySubscribers($comment->entry));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);

        $this->notifyMagazine(new EntryCommentCreatedNotification($comment->user, $comment));

        if (!count($usersToNotify)) {
            return;
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
                $iri,
                $this->getResponse($notification)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function getResponse(EntryCommentCreatedNotification $notification): string
    {
        return json_encode(
            [
                'op'   => 'EntryCommentNotificationManager',
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
