<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Message;
use App\Entity\MessageNotification;
use App\Entity\Post;
use App\Entity\PostNotification;
use App\Entity\User;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\PublisherInterface;

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
        $thread        = $message->getThread();
        $usersToNotify = $thread->getOtherParticipants($sender);

        foreach ($usersToNotify as $subscriber) {
            $notify = new MessageNotification($subscriber, $message);
            $this->entityManager->persist($notify);

            // @todo Send push notification to user
        }

        $this->entityManager->flush();
    }
}
