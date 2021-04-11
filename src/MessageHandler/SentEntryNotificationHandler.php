<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\EntryNotificationMessage;
use App\Service\NotificationManager;
use App\Repository\EntryRepository;

class SentEntryNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(EntryNotificationMessage $entryCreatedMessage)
    {
        $entry = $this->repository->find($entryCreatedMessage->entryId);
        if (!$entry) {
            return;
        }

        $this->manager->sendNewEntryNotification($entry);
    }
}
