<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\PostComment;
use App\Entity\PostCommentCreatedNotification;
use App\Entity\PostCommentDeletedNotification;
use App\Entity\PostCommentReplyNotification;
use App\Entity\User;
use App\Factory\MagazineFactory;
use App\Factory\UserFactory;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;
use function count;

class PostCommentNotificationManager
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

    public function sendCreated(PostComment $comment): void
    {
        $user = $this->sendUserReplyNotification($comment);
        $this->sendMagazineSubscribersNotification($comment, $user);
    }

    public function sendMagazineSubscribersNotification(PostComment $comment, ?User $exclude): void
    {
        $this->notifyMagazine(new PostCommentCreatedNotification($comment->user, $comment));

        $subs      = $this->getUsersToNotify($this->repository->findNewPostSubscribers($comment->post));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);
        $usersToNotify = $this->merge($usersToNotify, [$comment->post->user]);

        if($exclude) {
            $usersToNotify = array_filter($usersToNotify, fn($user) => $user !== $exclude);
        }

        foreach ($usersToNotify as $subscriber) {
            $notification = new PostCommentCreatedNotification($subscriber, $comment);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }

    private function notifyMagazine(PostCommentCreatedNotification $notification): void
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

    private function getCreatedResponse(PostCommentCreatedNotification $notification): string
    {
        return json_encode(
            [
                'op'   => 'PostCommentCreatedNotification',
                'id'   => $notification->getComment()->getId(),
                'data' => [],
                'html' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    private function sendUserReplyNotification(PostComment $comment): ?User
    {
        if(!$comment->parent || $comment->parent->isAuthor($comment->user)) {
            return null;
        }

        $notification = new PostCommentReplyNotification($comment->parent->user, $comment);
        $this->notifyUser($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification->user;
    }

    private function notifyUser(PostCommentReplyNotification $notification): void
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

    private function getReplyResponse(PostCommentReplyNotification $notification): string
    {
        return json_encode(
            [
                'op'   => 'PostCommentReplyNotification',
                'id'   => $notification->getComment()->getId(),
                'data' => [],
                'html' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendDeleted(PostComment $comment): void
    {
        $notification = new PostCommentDeletedNotification($comment->getUser(), $comment);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
