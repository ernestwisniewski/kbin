<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryNotificationMessage;
use App\Repository\EntryRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentEntryNotificationHandler implements MessageHandlerInterface
{
    private EntryRepository $entryRepository;
    private NotificationManager $notificationManager;

    public function __construct(EntryRepository $entryRepository, NotificationManager $notificationManager)
    {
        $this->entryRepository     = $entryRepository;
        $this->notificationManager = $notificationManager;
    }

    public function __invoke(EntryNotificationMessage $entryCreatedMessage)
    {
        $entry = $this->entryRepository->find($entryCreatedMessage->getEntryId());
        if (!$entry) {
            return;
        }

        $this->notificationManager->sendNewEntryNotification($entry);
    }
}
