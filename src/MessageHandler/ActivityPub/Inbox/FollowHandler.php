<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\User;
use App\Message\ActivityPub\Inbox\FollowMessage;
use App\Service\ActivityPubManager;
use App\Service\UserManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FollowHandler implements MessageHandlerInterface
{
    public function __construct(
        private ActivityPubManager $activityPubManager,
        private UserManager $userManager,
    ) {
    }

    public function __invoke(FollowMessage $message)
    {
        $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

        if ($message->payload['type'] === 'Follow') {
            $user  = $this->activityPubManager->getUserFromProfileId($message->payload['object']);

            $this->handleFollow($user, $actor);

            return;
        }

        if ($message->payload['type'] === 'Undo') {
            if ($message->payload['object']['type'] !== 'Follow') {
                return;
            }

            $user  = $this->activityPubManager->getUserFromProfileId($message->payload['object']['object']);

            $this->handleUnfollow($user, $actor);
        }
    }

    private function handleFollow(User $user, User $actor): void
    {
        $this->userManager->follow($actor, $user);
    }

    private function handleUnfollow(User $user, User $actor): void
    {
        $this->userManager->unfollow($actor, $user);
    }
}
