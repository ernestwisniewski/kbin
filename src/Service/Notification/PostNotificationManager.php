<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostNotification;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;
use function count;

class PostNotificationManager
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

    public function send(Post $post): void
    {
        $subs    = $this->getUsersToNotify($this->magazineSubscriptionRepository->findNewPostSubscribers($post));
        $follows = [];

        $usersToNotify = $this->merge($subs, $follows);

        $this->notifyMagazine($post, new PostNotification($post->user, $post));

        if (!count($usersToNotify)) {
            return;
        }

        foreach ($usersToNotify as $subscriber) {
            $notify = new PostNotification($subscriber, $post);
            $this->entityManager->persist($notify);
        }

        $this->entityManager->flush();
    }

    private function notifyMagazine(Post $post, PostNotification $notification)
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($post->magazine));

            $update = new Update(
                $iri,
                $this->getResponse($post, $notification)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function getResponse(Post $post, Notification $notification): string
    {
        return json_encode(
            [
                'postId'       => $post->getId(),
                'notification' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }
}
