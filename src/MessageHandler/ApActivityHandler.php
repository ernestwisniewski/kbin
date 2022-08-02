<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ApActivityMessage;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ApActivityHandler implements MessageHandlerInterface
{
    public function __construct()
    {
    }

    public function __invoke(ApActivityMessage $message)
    {
        $payload = @json_decode($message->payload, true);

        $cache = new FilesystemAdapter(); // @todo redis

        $key = 'ap_'.hash('sha256', $payload['id']);
        if ($cache->hasItem($key)) {
            return;
        }
        $item = $cache->getItem($key);
        $item->set(true);
        $cache->save($item);


    }
}
