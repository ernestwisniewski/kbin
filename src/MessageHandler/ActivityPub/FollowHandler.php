<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub;

use App\Entity\User;
use App\Message\ActivityPub\FollowMessage;
use App\Service\ActivityPubManager;
use App\Service\UserManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FollowHandler implements MessageHandlerInterface
{
    private array $object;

    public function __construct(private ActivityPubManager $activityPubManager, private UserManager $userManager)
    {
    }

    public function __invoke(FollowMessage $message)
    {
        $this->object = $message->payload;

        $user  = $this->activityPubManager->getUserFromProfileId($this->object['object']);
        $actor = $this->activityPubManager->findActorOrCreate($this->object['actor']);

        if ($this->object['type'] === 'Follow') {
            $this->handleFollow($user, $actor);
        }

        if ($this->object['type'] === 'Unfollow') {
            $this->handleUnfollow($user, $actor);
        }
    }

    private function handleFollow(User $user, User $actor): void
    {
        $this->userManager->follow($user, $actor);
    }

    private function handleUnfollow(User $user, User $actor): void
    {
        $this->userManager->unfollow($user, $actor);
    }
}
