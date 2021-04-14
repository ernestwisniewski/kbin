<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\BanNotificationMessage;
use App\Repository\MagazineBanRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentBanNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private MagazineBanRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(BanNotificationMessage $message)
    {
        $ban = $this->repository->find($message->banId);
        if (!$ban) {
            return;
        }

        $this->manager->sendBanNotification($ban);
    }
}
