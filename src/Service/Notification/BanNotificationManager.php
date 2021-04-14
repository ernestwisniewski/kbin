<?php declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\BanNotification;
use App\Entity\MagazineBan;
use App\Repository\MagazineBanRepository;
use Doctrine\ORM\EntityManagerInterface;

class BanNotificationManager
{
    use NotificationTrait;

    public function __construct(
        private MagazineBanRepository $repository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function send(MagazineBan $ban): void
    {
        $notification = new BanNotification($ban->user, $ban);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
