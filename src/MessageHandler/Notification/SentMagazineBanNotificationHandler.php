<?php declare(strict_types = 1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\MagazineBanNotificationMessage;
use App\Repository\MagazineBanRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentMagazineBanNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private MagazineBanRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(MagazineBanNotificationMessage $message)
    {
        $ban = $this->repository->find($message->banId);

        if (!$ban) {
            throw new UnrecoverableMessageHandlingException('Ban not found');
        }

        $this->manager->sendMagazineBanNotification($ban);
    }
}
