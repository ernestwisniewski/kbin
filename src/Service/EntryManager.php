<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\EntryDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Entity\User;
use App\Event\Entry\EntryBeforeDeletedEvent;
use App\Event\Entry\EntryBeforePurgeEvent;
use App\Event\Entry\EntryCreatedEvent;
use App\Event\Entry\EntryDeletedEvent;
use App\Event\Entry\EntryEditedEvent;
use App\Event\Entry\EntryPinEvent;
use App\Event\Entry\EntryRestoredEvent;
use App\Exception\UserBannedException;
use App\Factory\EntryFactory;
use App\Message\DeleteImageMessage;
use App\Repository\EntryRepository;
use App\Service\Contracts\ContentManagerInterface;
use App\Utils\Slugger;
use App\Utils\UrlCleaner;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class EntryManager implements ContentManagerInterface
{
    public function __construct(
        private readonly TagManager $tagManager,
        private readonly MentionManager $mentionManager,
        private readonly EntryCommentManager $entryCommentManager,
        private readonly UrlCleaner $urlCleaner,
        private readonly Slugger $slugger,
        private readonly BadgeManager $badgeManager,
        private readonly EntryFactory $factory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RateLimiterFactory $entryLimiter,
        private readonly MessageBusInterface $bus,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryRepository $entryRepository,
        private readonly CacheInterface $cache
    ) {
    }

    public function create(EntryDto $dto, User $user, bool $limiter = true): Entry
    {
        if ($limiter) {
            $limiter = $this->entryLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $entry = $this->factory->createFromDto($dto, $user);

        if ($dto->magazine->isBanned($user)) {
            throw new UserBannedException();
        }

        $entry->lang = $dto->lang;
        $entry->isAdult = $dto->isAdult || $entry->magazine->isAdult;
        $entry->slug = $this->slugger->slug($dto->title);
        $entry->image = $dto->image;
        if ($entry->image && !$entry->image->altText) {
            $entry->image->altText = $dto->imageAlt;
        }
        $entry->tags = $dto->tags ? $this->tagManager->extract(
            implode(' ', array_map(fn($tag) => str_starts_with($tag, '#') ? $tag : '#'.$tag, $dto->tags)),
            $entry->magazine->name
        ) : null;
        $entry->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $entry->visibility = $dto->visibility;
        $entry->apId = $dto->apId;
        $entry->magazine->lastActive = new \DateTime();
        $entry->user->lastActive = new \DateTime();
        $entry->lastActive = $dto->lastActive ?? $entry->lastActive;
        $entry->createdAt = $dto->createdAt ?? $entry->createdAt;
        if (empty($entry->body) && null === $entry->image && null === $entry->url) {
            throw new \Exception('Entry body and image cannot be empty');
        }

        $entry = $this->setType($dto, $entry);

        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCreatedEvent($entry));

        return $entry;
    }

    private function setType(EntryDto $dto, Entry $entry): Entry
    {
        $isImageUrl = false;
        if ($dto->url) {
            $entry->url = ($this->urlCleaner)($dto->url);
            $isImageUrl = ImageManager::isImageUrl($dto->url);
        }

        if (($dto->image && !$dto->body) || $isImageUrl) {
            $entry->type = Entry::ENTRY_TYPE_IMAGE;
            $entry->hasEmbed = true;

            return $entry;
        }

        if ($dto->url) {
            $entry->type = Entry::ENTRY_TYPE_LINK;

            return $entry;
        }

        if ($dto->body) {
            $entry->type = Entry::ENTRY_TYPE_ARTICLE;
            $entry->hasEmbed = false;
        }

        return $entry;
    }

    public function edit(Entry $entry, EntryDto $dto): Entry
    {
        Assert::same($entry->magazine->getId(), $dto->magazine->getId());

        $entry->title = $dto->title;
        $entry->url = $dto->url;
        $entry->body = $dto->body;
        $entry->lang = $dto->lang;
        $entry->isAdult = $dto->isAdult || $entry->magazine->isAdult;
        $entry->slug = $this->slugger->slug($dto->title);
        $entry->visibility = $dto->visibility;
        $oldImage = $entry->image;
        if ($dto->image) {
            $entry->image = $dto->image;
        }
        $entry->tags = $dto->tags ? $this->tagManager->extract(
            implode(' ', array_map(fn($tag) => str_starts_with($tag, '#') ? $tag : '#'.$tag, $dto->tags)),
            $entry->magazine->name
        ) : null;
        $entry->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $entry->isOc = $dto->isOc;
        $entry->lang = $dto->lang;
        $entry->editedAt = new \DateTimeImmutable('@'.time());
        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }
        if (empty($entry->body) && null === $entry->image && null === $entry->url) {
            throw new \Exception('Entry body and image cannot be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $entry->image !== $oldImage) {
            $this->bus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->dispatcher->dispatch(new EntryEditedEvent($entry));

        return $entry;
    }

    public function delete(User $user, Entry $entry): void
    {
        if ($user->apDomain && $user->apDomain !== parse_url($entry->apId, PHP_URL_HOST)) {
            return;
        }

        if ($entry->isAuthor($user) && $entry->comments->isEmpty()) {
            $this->purge($entry);

            return;
        }

        $entry->isAuthor($user) ? $entry->softDelete() : $entry->trash();

        $this->dispatcher->dispatch(new EntryBeforeDeletedEvent($entry, $user));

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryDeletedEvent($entry, $user));
    }

    public function purge(Entry $entry): void
    {
        $this->dispatcher->dispatch(new EntryBeforePurgeEvent($entry));

        $image = $entry->image?->filePath;

        $sort = new Criteria(null, ['createdAt' => Criteria::DESC]);
        foreach ($entry->comments->matching($sort) as $comment) {
            $this->entryCommentManager->purge($comment);
        }

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        if ($image) {
            $this->bus->dispatch(new DeleteImageMessage($image));
        }
    }

    public function restore(User $user, Entry $entry): void
    {
        if (VisibilityInterface::VISIBILITY_TRASHED !== $entry->visibility) {
            throw new \Exception('Invalid visibility');
        }

        $entry->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryRestoredEvent($entry, $user));
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

    public function detachImage(Entry $entry): void
    {
        $image = $entry->image->filePath;

        $entry->image = null;

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }

    public function getSortRoute(string $sortBy): string
    {
        return strtolower($this->translator->trans($sortBy));
    }

    public function changeMagazine(Entry $entry, Magazine $magazine): void
    {
        $this->entityManager->beginTransaction();

        try {
            $oldMagazine = $entry->magazine;
            $entry->magazine = $magazine;

            foreach ($entry->comments as $comment) {
                $comment->magazine = $magazine;
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return;
        }

        $oldMagazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($oldMagazine);
        $oldMagazine->entryCount = $this->entryRepository->countEntriesByMagazine($oldMagazine);

        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine);
        $magazine->entryCount = $this->entryRepository->countEntriesByMagazine($magazine);

        $this->entityManager->flush();

        $this->cache->invalidateTags(['entry_'.$entry->getId()]);
    }
}
