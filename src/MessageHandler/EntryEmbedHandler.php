<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\EntryCreatedMessage;
use App\Repository\EntryRepository;
use App\Utils\Embed;

class EntryEmbedHandler implements MessageHandlerInterface
{
    private EntryRepository $entryRepository;
    private Embed $embed;

    private EntityManagerInterface $entityManager;

    public function __construct(EntryRepository $entryRepository, Embed $embed, EntityManagerInterface $entityManager)
    {

        $this->entryRepository = $entryRepository;
        $this->embed = $embed;
        $this->entityManager = $entityManager;
    }

    public function __invoke(EntryCreatedMessage $entryCreatedMessage)
    {
        $entry = $this->entryRepository->find($entryCreatedMessage->getEntryId());

        if (!$entry || !$entry->getUrl()) {
            return;
        }

        $entry->setEmbed(
            ($this->embed->fetch($entry->getUrl()))->getEmbed()
        );

        $this->entityManager->flush();
    }
}
