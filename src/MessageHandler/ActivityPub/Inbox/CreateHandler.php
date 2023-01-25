<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Message\ActivityPub\Inbox\ChainActivityMessage;
use App\Message\ActivityPub\Inbox\CreateMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\Note;
use App\Service\ActivityPub\Page;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateHandler implements MessageHandlerInterface
{
    private array $object;

    public function __construct(
        private readonly Note $note,
        private readonly Page $page,
        private readonly MessageBusInterface $bus,
        private readonly ApActivityRepository $repository
    ) {
    }

    public function __invoke(CreateMessage $message)
    {
        $this->object = $message->payload;

        if ('Note' === $this->object['type']) {
            $this->handleChain();
        }

        if ('Page' === $this->object['type']) {
            $this->handlePage();
        }

        if ('Article' === $this->object['type']) {
            $this->handlePage();
        }

        if ('Question' === $this->object['type']) {
            $this->handleChain();
        }
    }

    private function handleChain()
    {
        if (isset($this->object['inReplyTo']) && $this->object['inReplyTo']) {
            $existed = $this->repository->findByObjectId($this->object['inReplyTo']);
            if (!$existed) {
                $this->bus->dispatch(new ChainActivityMessage([$this->object]));

                return;
            }
        }

        $this->note->create($this->object);
    }

    private function handlePage()
    {
        $this->page->create($this->object);
    }
}
