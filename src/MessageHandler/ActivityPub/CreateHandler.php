<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub;

use App\Message\ActivityPub\ChainActivityMessage;
use App\Message\ActivityPub\CreateMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\Note;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateHandler implements MessageHandlerInterface
{
    private array $object;

    public function __construct(private Note $note, private MessageBusInterface $bus, private ApActivityRepository $repository)
    {
    }

    public function __invoke(CreateMessage $message)
    {
        $this->object = $message->payload;

        if ($this->object['type'] === 'Note') {
            $this->handleNote();
        }

        if ($this->object['type'] === 'Page') {
            $this->handlePage();
        }
    }

    private function handlePage()
    {
    }

    private function handleNote()
    {
        if ($this->object['inReplyTo']) {
            $existed = $this->repository->findByObjectId($this->object['inReplyTo']);
            if (!$existed) {
                $this->bus->dispatch(new ChainActivityMessage([$this->object]));

                return;
            }
        };

        $this->note->create($this->object);
    }
}
