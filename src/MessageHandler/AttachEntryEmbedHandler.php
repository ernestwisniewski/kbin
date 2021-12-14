<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Entry;
use App\Entity\Image;
use App\Message\EntryEmbedMessage;
use App\Repository\EntryRepository;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use App\Utils\Embed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AttachEntryEmbedHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryRepository $entryRepository,
        private Embed $embed,
        private ImageManager $manager,
        private ImageRepository $imageRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(EntryEmbedMessage $message): void
    {
        $entry = $this->entryRepository->find($message->entryId);

        if (!$entry) {
            throw new UnrecoverableMessageHandlingException('Entry not found');
        }

        if (!$entry->url) {
            return;
        }

        try {
            $embed = $this->embed->fetch($entry->url);
        } catch (\Exception $e) {
            return;
        }

        $html    = $embed->html;
        $type    = $embed->getType();
        $isImage = $embed->isImageUrl();

        $cover = $this->fetchCover($entry, $embed);

        if (!$html && !$cover && !$isImage) {
            return;
        }

        $this->entityManager->transactional(
            static function () use ($entry, $cover, $html, $isImage, $type): void {
                $entry->type     = $type;
                $entry->hasEmbed = $html || $isImage;
                $entry->image    = $cover;
            }
        );
    }

    private function fetchCover(Entry $entry, Embed $embed): ?Image
    {
        if (!$entry->image) {
            $tempFile = null;
            if ($embed->image) {
                $tempFile = $this->fetchImage($embed->image);
            } elseif ($embed->isImageUrl()) {
                $tempFile = $this->fetchImage($entry->url);
            }

            if ($tempFile) {
                return $this->imageRepository->findOrCreateFromPath($tempFile);
            }
        }

        return null;
    }

    private function fetchImage(string $url): ?string
    {
        try {
            return $this->manager->download($url);
        } catch (\Exception $e) {
            return null;
        }
    }
}
