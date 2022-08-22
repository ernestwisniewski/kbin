<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class DeleteHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $repository,
        private DeleteWrapper $deleteWrapper,
        private MessageBusInterface $bus,
        private ActivityPubManager $activityPubManager,
        private SettingsManager $settingsManager
    ) {
    }

    public function __invoke(DeleteMessage $message): void
    {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $entity = $this->entityManager->getRepository($message->type)->find($message->id);

        $activity = $this->deleteWrapper->build($entity, Uuid::v4()->toRfc4122());

        $followers = $this->repository->findAudience($entity->user);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }

        $followers = $this->activityPubManager->createCcFromObject($activity, $entity->user);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower, $activity));
        }
    }
}
