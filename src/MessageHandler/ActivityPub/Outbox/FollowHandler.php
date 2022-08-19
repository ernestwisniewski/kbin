<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\FollowMessage;
use App\Repository\UserRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\FollowWrapper;
use App\Service\ActivityPub\Wrapper\UndoWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Uid\Uuid;

class FollowHandler implements MessageHandlerInterface
{
    public function __construct(
        private UserRepository $repository,
        private ActivityPubManager $activityPubManager,
        private FollowWrapper $followWrapper,
        private UndoWrapper $undoWrapper,
        private ApHttpClient $apHttpClient,
        private SettingsManager $settingsManager
    ) {
    }

    #[ArrayShape(['@context' => "string", 'id' => "string", 'actor' => "string", 'object' => "string"])] public function __invoke(
        FollowMessage $message
    ): void {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $id = Uuid::v4()->toRfc4122(); // todo save ap event stream

        $follower  = $this->repository->find($message->followerId);
        $following = $this->repository->find($message->followingId);

        $followObject = $this->followWrapper->build(
            $this->activityPubManager->getActorProfileId($follower),
            $followingProfileId = $this->activityPubManager->getActorProfileId($following),
            $id
        );

        if ($message->unfollow) {
            $followObject = $this->undoWrapper->build($followObject, $id);
        }

        $inbox = $this->apHttpClient->getInboxUrl($followingProfileId);

        $this->apHttpClient->post($inbox, $follower, $followObject);
    }
}
