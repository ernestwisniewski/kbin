<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\ChainActivityMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Note;
use App\Service\ActivityPub\Page;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ChainActivityHandler implements MessageHandlerInterface
{
    public function __construct(
        private ApHttpClient $client,
        private MessageBusInterface $bus,
        private ApActivityRepository $repository,
        private Note $note,
        private Page $page
    ) {
    }

    public function __invoke(ChainActivityMessage $message): void
    {
        if ($message->parent) {
            $this->unloadStack($message->chain, $message->parent, $message->announce);

            return;
        }

        $object = end($message->chain);

        // Handle parent objects
        if (isset($object['inReplyTo']) && $object['inReplyTo']) {
            $existed = $this->repository->findByObjectId($object['inReplyTo']);

            if ($existed) {
                $this->bus->dispatch(new ChainActivityMessage($message->chain, $existed, $message->announce));
            }

            $message->chain[] = $this->client->getActivityObject($object['inReplyTo']);
            $this->bus->dispatch(new ChainActivityMessage($message->chain, null, $message->announce));

            return;
        }

        // Create root object
        $entity = match ($this->getType($object)) {
            'Note' => $this->note->create($object),
            'Page' => $this->page->create($object),
            default => null
        };

        if (!$entity) {
            if ($message->announce && $message->announce['object'] === $object['object']) {

                $this->bus->dispatch(new ChainActivityMessage($message->chain, ['Announce' => true], $message->announce));

                return;
            }

            return;
        }

        array_pop($message->chain);

        $this->bus->dispatch(
            new ChainActivityMessage($message->chain, [
                'id'   => $entity->getId(),
                'type' => get_class($entity),
            ])
        );
    }

    private function unloadStack(array $chain, array $parent, ?array $announce = null): void
    {
        $object = end($chain);

        $entity = match ($this->getType($object)) {
            'Note' => $this->note->create($object),
            'Page' => $this->page->create($object),
            default => null
        };

        if (!$entity) {
            if ($announce && $announce['object'] === $object['object']) {

                $this->bus->dispatch(new AnnounceMessage($announce));

                return;
            }
        }

        array_pop($chain);

        if (count($chain)) {
            $this->bus->dispatch(new ChainActivityMessage($chain, $parent));
        }
    }

    private function getType(array $object): string
    {
        if (isset($object['object']) && is_array($object['object'])) {
            return $object['object']['type'];
        }

        return $object['type'];
    }
}
