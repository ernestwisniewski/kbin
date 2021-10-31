<?php declare(strict_types = 1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryCreatedNotification;
use App\Entity\EntryDeletedNotification;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
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
        private MagazineSubscriptionRepository $repository,
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

        $subs      = $this->getUsersToNotify($this->repository->findNewEntrySubscribers($subject));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);

        foreach ($usersToNotify as $subscriber) {
            $notification = new EntryCreatedNotification($subscriber, $subject);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }

    private function notifyMagazine(EntryCreatedNotification $notification): void
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

    private function getResponse(EntryCreatedNotification $notification): string
    {
        return json_encode(
            [
                'op'       => 'EntryCreatedNotification',
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
        $notification = new EntryDeletedNotification($subject->getUser(), $subject);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
