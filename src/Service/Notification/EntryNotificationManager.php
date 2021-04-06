<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Entry;
use App\Entity\EntryNotification;
use App\Entity\Notification;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

class EntryNotificationManager
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

    public function send(Entry $entry): void
    {
        $subs      = $this->getUsersToNotify($this->magazineSubscriptionRepository->findNewEntrySubscribers($entry));
        $followers = [];

        $usersToNotify = $this->merge($subs, $followers);

        $this->notifyMagazine($entry, new EntryNotification($entry->getUser(), $entry));

        if (!\count($usersToNotify)) {
            return;
        }

        foreach ($usersToNotify as $subscriber) {
            $notification = new EntryNotification($subscriber, $entry);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }

    private function getResponse(Entry $entry, Notification $notification): string
    {
        return json_encode(
            [
                'entryId'      => $entry->getId(),
                'notification' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    private function notifyMagazine(Entry $entry, EntryNotification $notification)
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($entry->getMagazine()));

            $update = new Update(
                $iri,
                $this->getResponse($entry, $notification)
            );

            ($this->publisher)($update);

        } catch (\Exception $e) {
        }
    }
}
