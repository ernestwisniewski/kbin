<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Post;
use App\Entity\PostNotification;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\PublisherInterface;

class PostNotificationManager
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

    public function send(Post $post): void
    {
        $subs    = $this->getUsersToNotify($this->magazineSubscriptionRepository->findNewPostSubscribers($post));
        $follows = [];

        $usersToNotify = $this->merge($subs, $follows);

        foreach ($usersToNotify as $subscriber) {
            $notify = new PostNotification($subscriber, $post);
            $this->entityManager->persist($notify);

            // @todo Send push notification to user
        }

        $this->entityManager->flush();
    }
}
