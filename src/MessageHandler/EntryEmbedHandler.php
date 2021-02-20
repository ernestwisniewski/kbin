<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\EntryCreatedMessage;
use App\Repository\ImageRepository;
use App\Repository\EntryRepository;
use App\Service\ImageManager;
use App\Utils\Embed;

class EntryEmbedHandler implements MessageHandlerInterface
{
    private EntryRepository $entryRepository;
    private Embed $embed;
    private EntityManagerInterface $entityManager;
    private ImageRepository $imageRepository;
    private ImageManager $imageManager;

    public function __construct(
        EntryRepository $entryRepository,
        Embed $embed,
        ImageManager $imageManager,
        ImageRepository $imageRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->entryRepository = $entryRepository;
        $this->embed           = $embed;
        $this->imageManager    = $imageManager;
        $this->imageRepository = $imageRepository;
        $this->entityManager   = $entityManager;
    }

    public function __invoke(EntryCreatedMessage $entryCreatedMessage)
    {
        $entry = $this->entryRepository->find($entryCreatedMessage->getEntryId());
        if (!$entry || !$entry->getUrl()) {
            return;
        }

        $embed = $this->embed->fetch($entry->getUrl());

        $image = null;
        if ($tempFile = $this->fetchImage($embed)) {
            $image = $this->imageRepository->findOrCreateFromPath($tempFile);
        }

        $html = $embed->getHtml();

        $this->entityManager->transactional(
            static function () use ($entry, $image, $html): void {
                $entry->setEmbed($html);
                $entry->setImage($image);
            }
        );
    }

    private function fetchImage(Embed $embed): ?string
    {
        if (!$embed->getImage()) {
            return null;
        }

        $tempFile = $this->imageManager->download($embed->getImage());
        if (!$tempFile) {
            return null;
        }

        return $tempFile;
    }
}
