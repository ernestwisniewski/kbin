<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\EntryDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\User;
use App\Event\Entry\EntryBeforePurgeEvent;
use App\Event\Entry\EntryCreatedEvent;
use App\Event\Entry\EntryDeletedEvent;
use App\Event\Entry\EntryEditedEvent;
use App\Event\Entry\EntryPinEvent;
use App\Event\Entry\EntryRestoredEvent;
use App\Factory\EntryFactory;
use App\Message\DeleteImageMessage;
use App\Service\Contracts\ContentManagerInterface;
use App\Utils\Slugger;
use App\Utils\UrlCleaner;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class EntryManager implements ContentManagerInterface
{
    public function __construct(
        private TagManager $tagManager,
        private UrlCleaner $urlCleaner,
        private Slugger $slugger,
        private BadgeManager $badgeManager,
        private EntryFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private RateLimiterFactory $entryLimiter,
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(EntryDto $dto, User $user): Entry
    {
        $limiter = $this->entryLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $entry                       = $this->factory->createFromDto($dto, $user);
        $entry->slug                 = $this->slugger->slug($dto->title);
        $entry->lang                 = $dto->lang;
        $entry->image                = $dto->image;
        $entry->tags                 = $dto->tags ? $this->tagManager->extract(
            implode(' ', array_map(fn($tag) => str_starts_with($tag, '#') ? $tag : '#'.$tag, $dto->tags))
        ) : null;
        $entry->magazine->lastActive = new \DateTime();
        $entry->magazine->addEntry($entry);

        $entry = $this->setType($dto, $entry);

        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCreatedEvent($entry));

        return $entry;
    }

    public function edit(Entry $entry, EntryDto $dto): Entry
    {
        Assert::same($entry->magazine->getId(), $dto->magazine->getId());

        $entry->title   = $dto->title;
        $entry->url     = $dto->url;
        $entry->body    = $dto->body;
        $entry->isAdult = $dto->isAdult;
        $entry->slug    = $this->slugger->slug($dto->title);
        $oldImage       = $entry->image;
        $entry->image   = $dto->image;
        $entry->tags    = $dto->tags ? $this->tagManager->extract(
            implode(' ', array_map(fn($tag) => str_starts_with($tag, '#') ? $tag : '#'.$tag, $dto->tags))
        ) : null;

        $entry->isOc = $dto->isOc;
        $entry->lang = $dto->lang;

        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }

        $this->entityManager->flush();

        if ($oldImage && $dto->image !== $oldImage) {
            $this->bus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->dispatcher->dispatch(new EntryEditedEvent($entry));

        return $entry;
    }

    public function delete(User $user, Entry $entry): void
    {
        if ($entry->isAuthor($user) && $entry->comments->isEmpty()) {
            $this->purge($entry);

            return;
        }

        $entry->isAuthor($user) ? $entry->softDelete() : $entry->trash();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryDeletedEvent($entry, $user));
    }

    public function restore(User $user, Entry $entry): void
    {
        if ($entry->visibility !== VisibilityInterface::VISIBILITY_TRASHED) {
            throw new \Exception('Invalid visibility');
        }

        $entry->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryRestoredEvent($entry, $user));
    }

    public function purge(Entry $entry): void
    {
        $this->dispatcher->dispatch(new EntryBeforePurgeEvent($entry));

        $image = $entry->image?->filePath;

        $entry->magazine->removeEntry($entry);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        if ($image) {
            $this->bus->dispatch(new DeleteImageMessage($image));
        }
    }

    public function pin(Entry $entry): Entry
    {
        $entry->sticky = !$entry->sticky;

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryPinEvent($entry));

        return $entry;
    }

    public function createDto(Entry $entry): EntryDto
    {
        return $this->factory->createDto($entry);
    }

    private function setType(EntryDto $dto, Entry $entry): Entry
    {
        if ($dto->url) {
            $entry->type = Entry::ENTRY_TYPE_LINK;
            $entry->url  = ($this->urlCleaner)($dto->url);
        }

        if ($dto->image) {
            $entry->type     = Entry::ENTRY_TYPE_IMAGE;
            $entry->hasEmbed = true;
        }

        if ($dto->body) {
            $entry->type     = Entry::ENTRY_TYPE_ARTICLE;
            $entry->hasEmbed = false;
        }

        return $entry;
    }

    public function detachImage(Entry $entry): void
    {
        $image = $entry->image->filePath;

        $entry->image = null;

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }
}
