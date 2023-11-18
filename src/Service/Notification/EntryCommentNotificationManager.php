<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryComment;
use App\Entity\EntryCommentCreatedNotification;
use App\Entity\EntryCommentDeletedNotification;
use App\Entity\EntryCommentEditedNotification;
use App\Entity\EntryCommentMentionedNotification;
use App\Entity\EntryCommentReplyNotification;
use App\Entity\Notification;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Kbin\User\Factory\UserFactory;
use App\Repository\MagazineLogRepository;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use App\Service\GenerateHtmlClassService;
use App\Service\ImageManager;
use App\Service\MentionManager;
use App\Service\SettingsManager;
use App\Utils\IriGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class EntryCommentNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private readonly MentionManager $mentionManager,
        private readonly NotificationRepository $notificationRepository,
        private readonly MagazineLogRepository $magazineLogRepository,
        private readonly MagazineSubscriptionRepository $magazineRepository,
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
         * @var EntryComment $subject
         */
        $users = $this->sendMentionedNotification($subject);
        $users = $this->sendUserReplyNotification($subject, $users);
        $this->sendMagazineSubscribersNotification($subject, $users);
    }

    private function sendMentionedNotification(EntryComment $subject): array
    {
        $users = [];
        $mentions = MentionManager::clearLocal($this->mentionManager->extract($subject->body));

        foreach ($this->mentionManager->getUsersFromArray($mentions) as $user) {
            if (!$user->apId) {
                $notification = new EntryCommentMentionedNotification($user, $subject);
                $this->entityManager->persist($notification);
            }

            $users[] = $user;
        }

        return $users;
    }

    private function sendUserReplyNotification(EntryComment $comment, array $exclude): array
    {
        if (!$comment->parent || $comment->parent->isAuthor($comment->user)) {
            return $exclude;
        }

        if (!$comment->parent->user->notifyOnNewEntryCommentReply) {
            return $exclude;
        }

        if (\in_array($comment->parent->user, $exclude)) {
            return $exclude;
        }

        if ($comment->parent->user->apId) {
            // @todo activtypub
            $exclude[] = $comment->parent->user;

            return $exclude;
        }

        $notification = new EntryCommentReplyNotification($comment->parent->user, $comment);
        $this->notifyUser($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $exclude[] = $notification->user;

        return $exclude;
    }

    private function notifyUser(EntryCommentReplyNotification $notification): void
    {
        if (false === $this->settingsManager->get('KBIN_MERCURE_ENABLED')) {
            return;
        }

        try {
            $iri = IriGenerator::getIriFromResource($notification->user);

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
        $class = explode('\\', $this->entityManager->getClassMetadata(\get_class($notification))->name);

        /**
         * @var EntryComment $comment ;
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
                    'id' => $comment->entry->getId(),
                    'htmlId' => $this->classService->fromEntity($comment->entry),
                ],
                'title' => $comment->entry->title,
                'body' => $comment->body,
                'icon' => $this->imageManager->getUrl($comment->image),
//                'image' => $this->imageManager->getUrl($comment->image),
                'url' => $this->urlGenerator->generate('entry_single', [
                        'magazine_name' => $comment->magazine->name,
                        'entry_id' => $comment->entry->getId(),
                        'slug' => $comment->entry->slug,
                    ]).'#entry-comment-'.$comment->getId(),
//                'toast' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    private function sendMagazineSubscribersNotification(EntryComment $comment, array $exclude): void
    {
        $this->notifyMagazine(new EntryCommentCreatedNotification($comment->user, $comment));

        $usersToNotify = []; // @todo user followers
        if ($comment->entry->user->notifyOnNewEntryReply && !$comment->isAuthor($comment->entry->user)) {
            $usersToNotify = $this->merge(
                $usersToNotify,
                [$comment->entry->user]
            );
        }

        if (\count($exclude)) {
            $usersToNotify = array_filter($usersToNotify, fn ($user) => !\in_array($user, $exclude));
        }

        foreach ($usersToNotify as $subscriber) {
            $notification = new EntryCommentCreatedNotification($subscriber, $comment);
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
            $iri = IriGenerator::getIriFromResource($notification->getComment()->magazine);

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
         * @var EntryComment $subject
         */
        $this->notifyMagazine(new EntryCommentEditedNotification($subject->user, $subject));
    }

    public function sendDeleted(ContentInterface $subject): void
    {
        /*
         * @var EntryComment $subject
         */
        $this->notifyMagazine($notification = new EntryCommentDeletedNotification($subject->user, $subject));
    }

    public function purgeNotifications(EntryComment $comment): void
    {
        $this->notificationRepository->removeEntryCommentNotifications($comment);
    }

    public function purgeMagazineLog(EntryComment $comment): void
    {
        $this->magazineLogRepository->removeEntryCommentLogs($comment);
    }
}
