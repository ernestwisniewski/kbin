<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostCreatedNotification;
use App\Entity\PostDeletedNotification;
use App\Entity\PostEditedNotification;
use App\Entity\PostMentionedNotification;
use App\Kbin\Factory\HtmlClassFactory;
use App\Kbin\Image\ImageUrlGet;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Repository\MagazineLogRepository;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\NotificationRepository;
use App\Service\Contracts\ContentNotificationManagerInterface;
use App\Service\MentionManager;
use App\Service\SettingsManager;
use App\Utils\IriGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class PostNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private readonly MentionManager $mentionManager,
        private readonly NotificationRepository $notificationRepository,
        private readonly MagazineLogRepository $magazineLogRepository,
        private readonly MagazineSubscriptionRepository $magazineRepository,
        private readonly MagazineFactory $magazineFactory,
        private readonly HubInterface $publisher,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageUrlGet $imageUrlGet,
        private readonly HtmlClassFactory $classService,
        private readonly SettingsManager $settingsManager
    ) {
    }

    // @todo check if author is on the block list
    public function sendCreated(ContentInterface $subject): void
    {
        /*
         * @var Post $subject
         */
        $this->notifyMagazine(new PostCreatedNotification($subject->user, $subject));

        // Notify mentioned
        $mentions = MentionManager::clearLocal($this->mentionManager->extract($subject->body));
        foreach ($this->mentionManager->getUsersFromArray($mentions) as $user) {
            $notification = new PostMentionedNotification($user, $subject);
            $this->entityManager->persist($notification);
        }

        // Notify subscribers
        $subscribers = $this->merge(
            $this->getUsersToNotify($this->magazineRepository->findNewPostSubscribers($subject)),
            [] // @todo user followers
        );

        $subscribers = array_filter($subscribers, fn ($s) => !\in_array($s->username, $mentions ?? []));

        foreach ($subscribers as $subscriber) {
            $notify = new PostCreatedNotification($subscriber, $subject);
            $this->entityManager->persist($notify);
        }

        $this->entityManager->flush();
    }

    private function notifyMagazine(Notification $notification)
    {
        if (false === $this->settingsManager->get('KBIN_MERCURE_ENABLED')) {
            return;
        }

        try {
            $iri = IriGenerator::getIriFromResource($notification->post->magazine);

            $update = new Update(
                ['pub', $iri],
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
         * @var Post $post ;
         */
        $post = $notification->post;

        return json_encode(
            [
                'op' => end($class),
                'id' => $post->getId(),
                'htmlId' => $this->classService->fromEntity($post),
                'magazine' => [
                    'name' => $post->magazine->name,
                ],
                'title' => $post->magazine->name,
                'body' => $post->body,
                'icon' => ($this->imageUrlGet)($post->image),
                'url' => $this->urlGenerator->generate('post_single', [
                    'magazine_name' => $post->magazine->name,
                    'post_id' => $post->getId(),
                    'slug' => $post->slug,
                ]),
//                'toast' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendEdited(ContentInterface $subject): void
    {
        /*
         * @var Post $subject
         */
        $this->notifyMagazine(new PostEditedNotification($subject->user, $subject));
    }

    public function sendDeleted(ContentInterface $post): void
    {
        /*
         * @var Post $post
         */
        $this->notifyMagazine($notification = new PostDeletedNotification($post->user, $post));
    }

    public function purgeNotifications(Post $post): void
    {
        $this->notificationRepository->removePostNotifications($post);
    }

    public function purgeMagazineLog(Post $post): void
    {
        $this->magazineLogRepository->removePostLogs($post);
    }
}
