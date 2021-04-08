<?php declare(strict_types=1);

namespace App\Service\Notification;

use App\Repository\MagazineSubscriptionRepository;
use Symfony\Component\Mercure\PublisherInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MessageNotification;
use App\Factory\MagazineFactory;
use App\Entity\Message;
use App\Entity\User;

class MessageNotificationManager
{
    use NotificationTrait;

    public function __construct(
        private MagazineSubscriptionRepository $magazineSubscriptionRepository,
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
        private PublisherInterface $publisher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function send(Message $message, User $sender): void
    {
        $thread        = $message->thread;
        $usersToNotify = $thread->getOtherParticipants($sender);

        foreach ($usersToNotify as $subscriber) {
            $notify = new MessageNotification($subscriber, $message);
            $this->entityManager->persist($notify);

            // @todo Send push notification to user
        }

        $this->entityManager->flush();
    }
}
