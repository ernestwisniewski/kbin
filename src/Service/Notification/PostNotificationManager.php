<?php declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Post;
use App\Entity\PostCreatedNotification;
use App\Entity\PostDeletedNotification;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

class PostNotificationManager implements ContentNotificationManagerInterface
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
         * @var Post $subject
         */
        $this->notifyMagazine($subject, new PostCreatedNotification($subject->user, $subject));

        $subs    = $this->getUsersToNotify($this->repository->findNewPostSubscribers($subject));
        $follows = [];

        $usersToNotify = $this->merge($subs, $follows);

        foreach ($usersToNotify as $subscriber) {
            $notify = new PostCreatedNotification($subscriber, $subject);
            $this->entityManager->persist($notify);
        }

        $this->entityManager->flush();
    }

    public function sendDeleted(ContentInterface $post): void
    {
        /**
         * @var Post $post
         */
        $notification = new PostDeletedNotification($post->getUser(), $post);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    private function notifyMagazine(Post $post, PostCreatedNotification $notification)
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($post->magazine));

            $update = new Update(
                ['pub', $iri],
                $this->getResponse($post, $notification)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function getResponse(Post $post, PostCreatedNotification $notification): string
    {
        return json_encode(
            [
                'op'       => 'PostCreatedNotification',
                'id'       => $post->getId(),
                'magazine' => [
                    'name' => $post->magazine->name,
                ],
                'toast'    => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }
}
