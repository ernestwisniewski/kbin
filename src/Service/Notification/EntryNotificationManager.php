<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryCreatedNotification;
use App\Entity\EntryDeletedNotification;
use App\Entity\EntryEditedNotification;
use App\Entity\Notification;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

class EntryNotificationManager implements ContentNotificationManagerInterface
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
         * @var Entry $subject
         */
        $this->notifyMagazine(new EntryCreatedNotification($subject->user, $subject));

        $subs      = $this->getUsersToNotify($this->magazineRepository->findNewEntrySubscribers($subject));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);

        foreach ($usersToNotify as $subscriber) {
            $notification = new EntryCreatedNotification($subscriber, $subject);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }

    public function sendEdited(ContentInterface $subject): void
    {
        /**
         * @var Entry $subject
         */
        $this->notifyMagazine(new EntryEditedNotification($subject->user, $subject));
    }

    private function notifyMagazine(Notification $notification): void
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($notification->entry->magazine));

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
                'id'       => $notification->entry->getId(),
                'magazine' => [
                    'name' => $notification->entry->magazine->name,
                ],
                'toast'    => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendDeleted(ContentInterface $subject): void
    {
        /**
         * @var Entry $subject
         */
        $this->notifyMagazine($notification = new EntryDeletedNotification($subject->user, $subject));
    }

    public function purgeNotifications(Entry $entry)
    {
        $notificationsIds = $this->notificationRepository->findEntryNotificationsIds($entry);

        foreach ($notificationsIds as $id) {
            $this->entityManager->remove($this->notificationRepository->find($id));
        }
    }
}
