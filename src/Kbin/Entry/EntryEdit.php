<?php

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Event\Entry\EntryEditedEvent;
use App\Kbin\BadgeManager;
use App\Kbin\MentionManager;
use App\Kbin\TagManager;
use App\Kbin\Utils\Slugger;
use App\Message\DeleteImageMessage;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

readonly class EntryEdit
{
    public function __construct(
        private TagManager $tagManager,
        private MentionManager $mentionManager,
        private BadgeManager $badgeManager,
        private Slugger $slugger,
        private ImageRepository $imageRepository,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Entry $entry, EntryDto $dto): Entry
    {
        Assert::same($entry->magazine->getId(), $dto->magazine->getId());

        $entry->title = $dto->title;
        $entry->url = $dto->url;
        $entry->body = $dto->body;
        $entry->lang = $dto->lang;
        $entry->isAdult = $dto->isAdult || $entry->magazine->isAdult;
        $entry->slug = $this->slugger->slug($dto->title);
        $entry->visibility = $dto->getVisibility();
        $oldImage = $entry->image;
        if ($dto->image && $dto->image->id !== $entry->image->getId()) {
            $entry->image = $this->imageRepository->find($dto->image->id);
        }
        $entry->tags = $dto->tags ? $this->tagManager->extract(
            implode(' ', array_map(fn ($tag) => str_starts_with($tag, '#') ? $tag : '#'.$tag, $dto->tags)),
            $entry->magazine->name
        ) : null;
        $entry->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $entry->isOc = $dto->isOc;
        $entry->lang = $dto->lang;
        $entry->editedAt = new \DateTimeImmutable('@'.time());
        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }

        if (empty($entry->body) && empty($entry->title) && null === $entry->image && null === $entry->url) {
            throw new \Exception('Entry body, name, url and image cannot all be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $entry->image !== $oldImage) {
            $this->messageBus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->eventDispatcher->dispatch(new EntryEditedEvent($entry));

        return $entry;
    }
}
