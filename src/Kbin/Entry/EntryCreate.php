<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Entity\User;
use App\Event\Entry\EntryCreatedEvent;
use App\Exception\UserBannedException;
use App\Kbin\Entry\Badge\EntryBadgeAssign;
use App\Kbin\Entry\DTO\EntryDto;
use App\Kbin\Entry\Factory\EntryFactory;
use App\Kbin\MentionManager;
use App\Kbin\Tag\TagExtract;
use App\Kbin\Utils\Slugger;
use App\Kbin\Utils\UrlCleaner;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class EntryCreate
{
    public function __construct(
        private TagExtract $tagExtract,
        private MentionManager $mentionManager,
        private EntryBadgeAssign $entryBadgeAssign,
        private Slugger $slugger,
        private UrlCleaner $urlCleaner,
        private EntryFactory $entryFactory,
        private ImageRepository $imageRepository,
        private RateLimiterFactory $entryLimiter,
        private RateLimiterFactory $spamProtection,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(EntryDto $dto, User $user, bool $rateLimit = true): Entry
    {
        if ($rateLimit) {
            $limiter = $this->entryLimiter->create($dto->ip);
            $spamProtection = $this->spamProtection->create($dto->ip);
            if (false === $limiter->consume()->isAccepted() && false === $spamProtection->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $entry = $this->entryFactory->createFromDto($dto, $user);

        if ($dto->magazine->isBanned($user)) {
            throw new UserBannedException();
        }

        $entry->lang = $dto->lang;
        $entry->isAdult = $dto->isAdult || $entry->magazine->isAdult;
        $entry->slug = $this->slugger->slug($dto->title);
        $entry->image = $dto->image ? $this->imageRepository->find($dto->image->id) : null;
        if ($entry->image && !$entry->image->altText) {
            $entry->image->altText = $dto->imageAlt;
        }
        $entry->tags = $dto->tags ? ($this->tagExtract)(
            implode(' ', array_map(fn ($tag) => str_starts_with($tag, '#') ? $tag : '#'.$tag, $dto->tags)),
            $entry->magazine->name
        ) : null;
        $entry->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $entry->visibility = $dto->getVisibility();
        $entry->apId = $dto->apId;
        $entry->magazine->lastActive = new \DateTime();
        $entry->user->lastActive = new \DateTime();
        $entry->lastActive = $dto->lastActive ?? $entry->lastActive;
        $entry->createdAt = $dto->createdAt ?? $entry->createdAt;
        if (empty($entry->body) && empty($entry->title) && null === $entry->image && null === $entry->url) {
            throw new \Exception('Entry body, name, url and image cannot all be empty');
        }

        $entry = $this->setType($dto, $entry);

        if ($dto->badges) {
            ($this->entryBadgeAssign)($entry, $dto->badges);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCreatedEvent($entry));

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
}
