<?php declare(strict_types=1);

namespace App\Service\Notification;

use App\Repository\MagazineSubscriptionRepository;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PostCommentNotification;
use Symfony\Component\Mercure\Update;
use App\Factory\MagazineFactory;
use App\Entity\PostComment;
use Twig\Environment;
use function count;

class PostCommentNotificationManager
{
    use NotificationTrait;

    public function __construct(
        private MagazineSubscriptionRepository $magazineSubscriptionRepository,
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
        private PublisherInterface $publisher,
        private Environment $twig,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function send(PostComment $comment): void
    {
        $subs      = $this->getUsersToNotify($this->magazineSubscriptionRepository->findNewPostSubscribers($comment->post));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);

        $this->notifyMagazine(new PostCommentNotification($comment->user, $comment));
        if (!count($usersToNotify)) {
            return;
        }

        foreach ($usersToNotify as $subscriber) {
            $notification = new PostCommentNotification($subscriber, $comment);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }

    private function getResponse(PostCommentNotification $notification): string
    {
        return json_encode(
            [
                'commentId'    => $notification->getComment()->getId(),
                'notification' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    private function notifyMagazine(PostCommentNotification $notification): void
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
}
