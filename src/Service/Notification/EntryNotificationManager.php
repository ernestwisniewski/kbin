<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryCreatedNotification;
use App\Entity\EntryDeletedNotification;
use App\Entity\EntryEditedNotification;
use App\Entity\EntryMentionedNotification;
use App\Entity\Magazine;
use App\Entity\Notification;
use App\Factory\MagazineFactory;
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

class EntryNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private NotificationRepository $notificationRepository,
        private MagazineSubscriptionRepository $magazineRepository,
        private MentionManager $mentionManager,
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
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
         * @var Entry $subject
         */
        $this->notifyMagazine(new EntryCreatedNotification($subject->user, $subject));

        // Notify mentioned
        foreach ($this->mentionManager->getUsersFromArray($subject->mentions) as $user) {
            $notification = new EntryMentionedNotification($user, $subject);
            $this->entityManager->persist($notification);
        }

        // Notify subscribers
        $subscribers = $this->merge(
            $this->getUsersToNotify($this->magazineRepository->findNewEntrySubscribers($subject)),
            [] // @todo user followers
        );

        $subscribers       = array_filter($subscribers, fn($s) => !in_array($s->username, $subject->mentions ?? []));

        foreach ($subscribers as $subscriber) {
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

            $this->publisher->publish($update);

        } catch (Exception $e) {
        }
    }

    private function getResponse(Notification $notification): string
    {
        $class = explode("\\", $this->entityManager->getClassMetadata(get_class($notification))->name);

        /**
         * @var Magazine $magazine
         * @var Entry    $entry
         */
        $entry    = $notification->entry;
        $magazine = $notification->entry->magazine;

        return json_encode(
            [
                'op'       => end($class),
                'id'       => $entry->getId(),
                'magazine' => [
                    'name' => $magazine->name,
                ],
                'title'    => $magazine->title,
                'body'     => $entry->title,
                'icon'     => null,
                'url'      => $this->urlGenerator->generate('entry_single', [
                    'magazine_name' => $magazine->name,
                    'entry_id'      => $entry->getId(),
                    'slug'          => $entry->slug,
                ]),
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
