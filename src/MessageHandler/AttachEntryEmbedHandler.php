<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Entry;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\EntryEmbedMessage;
use App\Repository\ImageRepository;
use App\Repository\EntryRepository;
use App\Service\ImageManager;
use App\Utils\Embed;

class AttachEntryEmbedHandler implements MessageHandlerInterface
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

    public function __invoke(EntryEmbedMessage $entryCreatedMessage)
    {
        $entry = $this->entryRepository->find($entryCreatedMessage->getEntryId());
        if (!$entry || !$entry->getUrl()) {
            return;
        }

        $embed = $this->embed->fetch($entry->getUrl());

        $cover    = null;
        $tempFile = null;
        if ($embed->getImage()) {
            $tempFile = $this->fetchImage($embed->getImage());
        } elseif ($embed->isImageUrl()) {
            $tempFile = $this->fetchImage($entry->getUrl());
        }

        if ($tempFile) {
            $cover = $this->imageRepository->findOrCreateFromPath($tempFile);
        }

        $html    = $embed->getHtml();
        $type    = $embed->getType();
        $isImage = $embed->isImageUrl();

        if (!$html && !$cover && !$isImage) {
            return;
        }

        $this->entityManager->transactional(
            static function () use ($entry, $cover, $html, $isImage, $type): void {
                $entry->setType($type);
                $entry->setHasEmbed($html || $isImage);
                $entry->setImage($cover);
            }
        );
    }

    private function fetchImage(string $url): ?string
    {
        return $this->imageManager->download($url);
    }
}
