<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\EntryNotificationMessage;
use App\Service\NotificationManager;
use App\Repository\EntryRepository;

class SentEntryNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryRepository $entryRepository,
        private NotificationManager $notificationManager
    ) {
    }

    public function __invoke(EntryNotificationMessage $entryCreatedMessage)
    {
        $entry = $this->entryRepository->find($entryCreatedMessage->entryId);
        if (!$entry) {
            return;
        }

        $this->notificationManager->sendNewEntryNotification($entry);
    }
}
