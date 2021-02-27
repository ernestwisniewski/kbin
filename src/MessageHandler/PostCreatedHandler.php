<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PostCreatedMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PostCreatedHandler implements MessageHandlerInterface
{
    public function __invoke(PostCreatedMessage $postCreatedMessage)
    {
    }
}
