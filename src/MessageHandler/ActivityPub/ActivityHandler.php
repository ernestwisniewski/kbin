<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub;

use App\Message\ActivityPub\ActivityMessage;
use App\Message\ActivityPub\CreateMessage;
use App\Message\ActivityPub\FollowMessage;
use App\Service\ActivityPub\SignatureValidator;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ActivityHandler implements MessageHandlerInterface
{
    public function __construct(private SignatureValidator $signatureValidator, private MessageBusInterface $bus)
    {
    }

    public function __invoke(ActivityMessage $message)
    {
        $payload = @json_decode($message->payload, true);

        $cache = new FilesystemAdapter(); // @todo redis

        $key = 'ap_'.hash('sha256', $payload['id']);
        if ($cache->hasItem($key)) {
//            return;
        }
        $item = $cache->getItem($key);
        $item->set(true);
        $cache->save($item);

        if ($message->headers) {
//            $this->signatureValidator->validate($message->payload, $message->headers);
        }

        $this->handle($payload);
    }

    private function handle(array $payload)
    {
        switch ($payload['type']) {
            case 'Create':
                $this->bus->dispatch(new CreateMessage($payload['object']));
                break;
            case 'Note':
            case 'Page':
                $this->bus->dispatch(new CreateMessage($payload));
                break;
            case 'Announce':
                $this->handleAnnounce($payload);
                break;
            case 'Follow':
            case 'Undo':
                $this->bus->dispatch(new FollowMessage($payload));
                break;
        }
    }

    private function handleAnnounce(array $payload)
    {
    }
}
