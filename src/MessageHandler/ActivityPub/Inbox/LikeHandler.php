<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\EventSubscriber\VoteHandleSubscriber;
use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\ChainActivityMessage;
use App\Message\ActivityPub\Inbox\LikeMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\FavouriteManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class LikeHandler implements MessageHandlerInterface
{

    public function __construct(
        private ActivityPubManager $activityPubManager,
        private ApActivityRepository $repository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private FavouriteManager $manager,
        private ApHttpClient $apHttpClient,
    ) {
    }

    public function __invoke(LikeMessage $message): void
    {
        if ($message->payload['type'] === 'Like') {
            $activity = $this->repository->findByObjectId($message->payload['object']);

            if ($activity) {
                $entity = $this->entityManager->getRepository($activity['type'])->find((int)$activity['id']);
            } else {
                $object = $this->apHttpClient->getActivityObject($message->payload['object']);

                $this->bus->dispatch(new ChainActivityMessage([$object], null, null, $message->payload));

                return;
            }

            $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

            $this->manager->toggle($actor, $entity, FavouriteManager::TYPE_LIKE);
        }

        if ($message->payload['type'] === 'Undo') {
            if($message->payload['object']['type'] === 'Like') {
                $activity = $this->repository->findByObjectId($message->payload['object']['object']);
                $entity = $this->entityManager->getRepository($activity['type'])->find((int)$activity['id']);
                $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

                $this->manager->toggle($actor, $entity, FavouriteManager::TYPE_UNLIKE);
            }
        }
    }
}
