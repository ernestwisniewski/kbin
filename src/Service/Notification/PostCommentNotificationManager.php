<?php

declare(strict_types=1);

namespace App\Service\Notification;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Notification;
use App\Entity\PostComment;
use App\Entity\PostCommentCreatedNotification;
use App\Entity\PostCommentDeletedNotification;
use App\Entity\PostCommentEditedNotification;
use App\Entity\PostCommentMentionedNotification;
use App\Entity\PostCommentReplyNotification;
use App\Factory\MagazineFactory;
use App\Factory\UserFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use App\Service\GenerateHtmlClassService;
use App\Service\ImageManager;
use App\Service\MentionManager;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class PostCommentNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private readonly MentionManager $mentionManager,
        private readonly NotificationRepository $notificationRepository,
        private readonly MagazineSubscriptionRepository $magazineRepository,
        private readonly IriConverterInterface $iriConverter,
        private readonly MagazineFactory $magazineFactory,
        private readonly UserFactory $userFactory,
        private readonly HubInterface $publisher,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager,
        private readonly GenerateHtmlClassService $classService,
        private readonly SettingsManager $settingsManager
    ) {
    }

    // @todo check if author is on the block list
    public function sendCreated(ContentInterface $subject): void
    {
        /**
         * @var PostComment $subject
         */
        $users = $this->sendMentionedNotification($subject);
        $users = $this->sendUserReplyNotification($subject, $users);
        $this->sendMagazineSubscribersNotification($subject, $users);
    }

    private function sendMentionedNotification(PostComment $subject): array
    {
        $users = [];
        $mentions = MentionManager::clearLocal($this->mentionManager->extract($subject->body));

        foreach ($this->mentionManager->getUsersFromArray($mentions) as $user) {
            if (!$user->apId) {
                $notification = new PostCommentMentionedNotification($user, $subject);
                $this->entityManager->persist($notification);
            }

            $users[] = $user;
        }

        return $users;
    }

    private function sendUserReplyNotification(PostComment $comment, array $exclude): array
    {
        if (!$comment->parent || $comment->parent->isAuthor($comment->user)) {
            return $exclude;
        }

        if (!$comment->parent->user->notifyOnNewPostCommentReply) {
            return $exclude;
        }

        if (in_array($comment->parent->user, $exclude)) {
            return $exclude;
        }

        if ($comment->parent->user->apId) {
            // @todo activtypub
            $exclude[] = $comment->parent->user;

            return $exclude;
        }

        $notification = new PostCommentReplyNotification($comment->parent->user, $comment);
        $this->notifyUser($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $exclude[] = $notification->user;

        return $exclude;
    }

    private function notifyUser(PostCommentReplyNotification $notification): void
    {
        if (false === $this->settingsManager->get('KBIN_MERCURE_ENABLED')) {
            return;
        }

        try {
            $iri = $this->iriConverter->getIriFromResource($this->userFactory->createDto($notification->user));

            $update = new Update(
                $iri,
                $this->getResponse($notification)
            );

            $this->publisher->publish($update);
        } catch (\Exception $e) {
        }
    }

    private function getResponse(Notification $notification): string
    {
        $class = explode('\\', $this->entityManager->getClassMetadata(get_class($notification))->name);

        /**
         * @var PostComment $comment
         */
        $comment = $notification->getComment();

        return json_encode(
            [
                'op' => end($class),
                'id' => $comment->getId(),
                'htmlId' => $this->classService->fromEntity($comment),
                'parent' => $comment->parent ? [
                    'id' => $comment->parent->getId(),
                    'htmlId' => $this->classService->fromEntity($comment->parent),
                ] : null,
                'parentSubject' => [
                    'id' => $comment->post->getId(),
                    'htmlId' => $this->classService->fromEntity($comment->post),
                ],
                'title' => $comment->post->body,
                'body' => $comment->body,
                'icon' => $this->imageManager->getUrl($comment->image),
//                'image' => $this->imageManager->getUrl($comment->image),
                'url' => $this->urlGenerator->generate('post_single', [
                        'magazine_name' => $comment->magazine->name,
                        'post_id' => $comment->post->getId(),
                        'slug' => $comment->post->slug,
                    ]).'#post-comment-'.$comment->getId(),
//                'toast' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendMagazineSubscribersNotification(PostComment $comment, array $exclude): void
    {
        $this->notifyMagazine(new PostCommentCreatedNotification($comment->user, $comment));

        $usersToNotify = []; // @todo user followers
        if ($comment->user->notifyOnNewPostReply && !$comment->isAuthor($comment->post->user)) {
            $usersToNotify = $this->merge(
                $usersToNotify,
                [$comment->post->user]
            );
        }

        if (count($exclude)) {
            $usersToNotify = array_filter($usersToNotify, fn ($user) => !in_array($user, $exclude));
        }

        foreach ($usersToNotify as $subscriber) {
            $notification = new PostCommentCreatedNotification($subscriber, $comment);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }

    private function notifyMagazine(Notification $notification): void
    {
        if (false === $this->settingsManager->get('KBIN_MERCURE_ENABLED')) {
            return;
        }

        try {
            $iri = $this->iriConverter->getIriFromResource(
                $this->magazineFactory->createDto($notification->getComment()->magazine)
            );

            $update = new Update(
                ['pub', $iri],
                $this->getResponse($notification)
            );

            $this->publisher->publish($update);
        } catch (\Exception $e) {
        }
    }

    public function sendEdited(ContentInterface $subject): void
    {
        /*
         * @var PostComment $subject
         */
        $this->notifyMagazine(new PostCommentEditedNotification($subject->user, $subject));
    }

    public function sendDeleted(ContentInterface $subject): void
    {
        /*
         * @var PostComment $subject
         */
        $this->notifyMagazine($notification = new PostCommentDeletedNotification($subject->user, $subject));
    }

    public function purgeNotifications(PostComment $comment)
    {
        $notificationsIds = $this->notificationRepository->findPostCommentNotificationsIds($comment);

        foreach ($notificationsIds as $id) {
            $this->entityManager->remove($this->notificationRepository->find($id));
        }
    }
}
