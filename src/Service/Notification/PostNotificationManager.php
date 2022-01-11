<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostCreatedNotification;
use App\Entity\PostDeletedNotification;
use App\Entity\PostEditedNotification;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

class PostNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private NotificationRepository $notificationRepository,
        private MagazineSubscriptionRepository $magazineRepository,
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
        private PublisherInterface $publisher,
        private Environment $twig,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendCreated(ContentInterface $subject): void
    {
        /**
         * @var Post $subject
         */
        $this->notifyMagazine(new PostCreatedNotification($subject->user, $subject));

        $subs    = $this->getUsersToNotify($this->magazineRepository->findNewPostSubscribers($subject));
        $follows = [];

        $usersToNotify = $this->merge($subs, $follows);

        foreach ($usersToNotify as $subscriber) {
            $notify = new PostCreatedNotification($subscriber, $subject);
            $this->entityManager->persist($notify);
        }

        $this->entityManager->flush();
    }

    public function sendEdited(ContentInterface $subject): void
    {
        /**
         * @var Post $subject
         */
        $this->notifyMagazine(new PostEditedNotification($subject->user, $subject));
    }

    private function notifyMagazine(Notification $notification)
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($notification->post->magazine));

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
                'op'       => end($class),
                'id'       => $notification->post->getId(),
                'magazine' => [
                    'name' => $notification->post->magazine->name,
                ],
                'toast'    => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendDeleted(ContentInterface $post): void
    {
        /**
         * @var Post $post
         */
        $this->notifyMagazine($notification = new PostDeletedNotification($post->user, $post));
    }

    public function purgeNotifications(Post $post)
    {
        $notificationsIds = $this->notificationRepository->findPostNotificationsIds($post);

        foreach ($notificationsIds as $id) {
            $this->entityManager->remove($this->notificationRepository->find($id));
        }
    }
}
