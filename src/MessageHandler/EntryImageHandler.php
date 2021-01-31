<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\EntryCreatedMessage;
use App\Repository\EntryRepository;
use App\Utils\Embed;

class EntryImageHandler implements MessageHandlerInterface
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

        $imageUrl = ($this->embed->fetch($entry->getUrl()))->getImage();
        if (!$imageUrl) {
            return;
        }

        $tempFile = $this->imageManager->download($imageUrl);
        if(!$tempFile) {
            return;
        }

        $image    = $this->imageRepository->findOrCreateFromPath($tempFile);

        $this->entityManager->transactional(
            static function () use ($entry, $image): void {
                $entry->setImage($image);
            }
        );
    }
}
