<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Factory\ActivityPub\ActivityFactory;
use App\Message\ActivityPub\Outbox\AnnounceMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\AnnounceWrapper;
use App\Service\ActivityPub\Wrapper\UndoWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class AnnounceHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AnnounceWrapper $announceWrapper,
        private readonly UndoWrapper $undoWrapper,
        private readonly ActivityPubManager $activityPubManager,
        private readonly ActivityFactory $activityFactory,
        private readonly MessageBusInterface $bus,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    #[ArrayShape([
        '@context' => 'string',
        'id' => 'string',
        'actor' => 'string',
        'object' => 'string',
    ])]
    public function __invoke(
        AnnounceMessage $message
    ): void {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $user = $this->userRepository->find($message->userId);
        $object = $this->entityManager->getRepository($message->objectType)->find($message->objectId);

        $activity = $this->announceWrapper->build(
            $this->activityPubManager->getActorProfileId($user),
            $this->activityFactory->create($object),
        );

        if ($message->removeAnnounce) {
            $activity = $this->undoWrapper->build($activity);
        }

        $this->deliver(array_filter($this->userRepository->findAudience($user)), $activity);
        $this->deliver(array_filter($this->activityPubManager->createInboxesFromCC($activity, $user)), $activity);
        $this->deliver(array_filter($this->magazineRepository->findAudience($object->magazine)), $activity);
        $this->deliver([$object->user->apInboxUrl], $activity);
    }

    private function deliver(array $followers, array $activity): void
    {
        foreach ($followers as $follower) {
            if (!$follower) {
                continue;
            }

            $inboxUrl = is_string($follower) ? $follower : $follower->apInboxUrl;

            if ($this->settingsManager->isBannedInstance($inboxUrl)) {
                continue;
            }

            $this->bus->dispatch(new DeliverMessage($inboxUrl, $activity));
        }
    }
}
