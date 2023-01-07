<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\FollowMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\FollowWrapper;
use App\Service\ActivityPub\Wrapper\UndoWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FollowHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly ActivityPubManager $activityPubManager,
        private readonly FollowWrapper $followWrapper,
        private readonly UndoWrapper $undoWrapper,
        private readonly ApHttpClient $apHttpClient,
        private readonly SettingsManager $settingsManager
    ) {
    }

    #[ArrayShape([
        '@context' => 'string',
        'id' => 'string',
        'actor' => 'string',
        'object' => 'string',
    ])]
 public function __invoke(
        FollowMessage $message
    ): void {
     if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
         return;
     }

     $follower = $this->userRepository->find($message->followerId);
     if ($message->magazine) {
         $following = $this->magazineRepository->find($message->followingId);
     } else {
         $following = $this->userRepository->find($message->followingId);
     }

     $followObject = $this->followWrapper->build(
         $this->activityPubManager->getActorProfileId($follower),
         $followingProfileId = $this->activityPubManager->getActorProfileId($following),
     );

     if ($message->unfollow) {
         $followObject = $this->undoWrapper->build($followObject);
     }

     $inbox = $this->apHttpClient->getInboxUrl($followingProfileId);

     $this->apHttpClient->post($inbox, $follower, $followObject);
 }
}
