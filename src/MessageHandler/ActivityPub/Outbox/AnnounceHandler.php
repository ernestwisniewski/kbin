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
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AnnounceHandler implements MessageHandlerInterface
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

     $this->deliver($this->userRepository->findAudience($user), $activity);
     $this->deliver($this->activityPubManager->createCcFromObject($activity, $user), $activity);
     $this->deliver($this->magazineRepository->findAudience($object->magazine), $activity);
 }

    private function deliver(array $followers, array $activity)
    {
        foreach ($followers as $follower) {
            if (is_string($follower)) {
                $this->bus->dispatch(new DeliverMessage($follower, $activity));

                return;
            }

            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }
    }
}
