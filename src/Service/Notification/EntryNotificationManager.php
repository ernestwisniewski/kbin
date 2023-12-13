<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryCreatedNotification;
use App\Entity\EntryDeletedNotification;
use App\Entity\EntryEditedNotification;
use App\Entity\EntryMentionedNotification;
use App\Entity\Magazine;
use App\Entity\Notification;
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

class EntryNotificationManager implements ContentNotificationManagerInterface
{
    use NotificationTrait;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly MagazineLogRepository $magazineLogRepository,
        private readonly MagazineSubscriptionRepository $magazineRepository,
        private readonly MentionManager $mentionManager,
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
         * @var Entry $subject
         */
        $this->notifyMagazine(new EntryCreatedNotification($subject->user, $subject));

        // Notify mentioned
        $mentions = MentionManager::clearLocal($this->mentionManager->extract($subject->body));
        foreach ($this->mentionManager->getUsersFromArray($mentions) as $user) {
            if (!$user->apId) {
                $notification = new EntryMentionedNotification($user, $subject);
                $this->entityManager->persist($notification);
            }
        }

        // Notify subscribers
        $subscribers = $this->merge(
            $this->getUsersToNotify($this->magazineRepository->findNewEntrySubscribers($subject)),
            [] // @todo user followers
        );

        $subscribers = array_filter($subscribers, fn ($s) => !\in_array($s->username, $mentions ?? []));

        foreach ($subscribers as $subscriber) {
            $notification = new EntryCreatedNotification($subscriber, $subject);
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
            $iri = IriGenerator::getIriFromResource($notification->entry->magazine);

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
         * @var Magazine $magazine
         * @var Entry    $entry
         */
        $entry = $notification->entry;
        $magazine = $notification->entry->magazine;

        return json_encode(
            [
                'op' => end($class),
                'id' => $entry->getId(),
                'htmlId' => $this->classService->fromEntity($entry),
                'magazine' => [
                    'name' => $magazine->name,
                ],
                'title' => $magazine->title,
                'body' => $entry->title,
                'icon' => ($this->imageUrlGet)($entry->image),
                'url' => $this->urlGenerator->generate('entry_single', [
                    'magazine_name' => $magazine->name,
                    'entry_id' => $entry->getId(),
                    'slug' => $entry->slug,
                ]),
//                'toast' => $this->twig->render('_layout/_toast.html.twig', ['notification' => $notification]),
            ]
        );
    }

    public function sendEdited(ContentInterface $subject): void
    {
        /*
         * @var Entry $subject
         */
        $this->notifyMagazine(new EntryEditedNotification($subject->user, $subject));
    }

    public function sendDeleted(ContentInterface $subject): void
    {
        /*
         * @var Entry $subject
         */
        $this->notifyMagazine($notification = new EntryDeletedNotification($subject->user, $subject));
    }

    public function purgeNotifications(Entry $entry): void
    {
        $this->notificationRepository->removeEntryNotifications($entry);
    }

    public function purgeMagazineLog(Entry $entry): void
    {
        $this->magazineLogRepository->removeEntryLogs($entry);
    }
}
