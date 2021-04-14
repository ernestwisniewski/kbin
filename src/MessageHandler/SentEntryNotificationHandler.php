<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryNotificationMessage;
use App\Repository\EntryRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentEntryNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(EntryNotificationMessage $message)
    {
        $entry = $this->repository->find($message->entryId);
        if (!$entry) {
            return;
        }

        $this->manager->sendNewEntryNotification($entry);
    }
}
