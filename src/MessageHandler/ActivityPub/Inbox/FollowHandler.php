<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\User;
use App\Message\ActivityPub\Inbox\FollowMessage;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\AcceptWrapper;
use App\Service\ActivityPubManager;
use App\Service\UserManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FollowHandler implements MessageHandlerInterface
{
    public function __construct(
        private ActivityPubManager $activityPubManager,
        private UserManager $userManager,
        private ApHttpClient $client,
        private AcceptWrapper $acceptWrapper
    ) {
    }

    public function __invoke(FollowMessage $message)
    {
        $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

        if ($message->payload['type'] === 'Follow') {
            $user = $this->activityPubManager->findActorOrCreate($message->payload['object']);

            // @todo activitypub create follow request if profile is private
            $this->handleFollow($user, $actor);

            $this->accept($message->payload, $user);

            return;
        }

        if ($message->payload['type'] === 'Undo') {
            if ($message->payload['object']['type'] !== 'Follow') {
                return;
            }

            $user = $this->activityPubManager->findActorOrCreate($message->payload['object']['object']);

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

    #[ArrayShape(['@context' => "string", 'id' => "string", 'type' => "string", 'actor' => "mixed", 'object' => "mixed"])] private function accept(
        array $payload,
        User $user
    ): void {

        $accept = $this->acceptWrapper->build(
            $payload['object'],
            $payload['actor'],
            $payload['id'],
        );

        $this->client->post($this->client->getInboxUrl($payload['actor']), $user, $accept);
    }
}
