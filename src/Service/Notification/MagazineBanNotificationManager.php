<?php declare(strict_types = 1);

namespace App\Service\Notification;

use App\Entity\MagazineBan;
use App\Entity\MagazineBanNotification;
use App\Repository\MagazineBanRepository;
use Doctrine\ORM\EntityManagerInterface;

class MagazineBanNotificationManager
{
    use NotificationTrait;

    public function __construct(
        private MagazineBanRepository $repository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function send(MagazineBan $ban): void
    {
        $notification = new MagazineBanNotification($ban->user, $ban);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
