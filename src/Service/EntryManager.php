<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;
use App\Event\Entry\EntryBeforePurgeEvent;
use App\Event\Entry\EntryCreatedEvent;
use App\Event\Entry\EntryDeletedEvent;
use App\Event\Entry\EntryPinEvent;
use App\Event\Entry\EntryUpdatedEvent;
use App\Factory\EntryFactory;
use App\Service\Contracts\ContentManagerInterface;
use App\Utils\Slugger;
use App\Utils\UrlCleaner;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class EntryManager implements ContentManagerInterface
{
    public function __construct(
        private EntryFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private BadgeManager $badgeManager,
        private UrlCleaner $urlCleaner,
        private Slugger $slugger,
        private RateLimiterFactory $postLimiter,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryDto $dto, User $user): Entry
    {
        $limiter = $this->postLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $entry                       = $this->factory->createFromDto($dto, $user);
        $entry->slug                 = $this->slugger->slug($dto->title);
        $entry->image                = $dto->image;
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
        $entry->image   = $dto->image;
        $entry->slug    = $this->slugger->slug($dto->title);

        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryUpdatedEvent($entry));

        return $entry;
    }

    public function delete(User $user, Entry $entry): void
    {
        if ($entry->isAuthor($user) && $entry->comments->isEmpty()) {
            $this->purge($user, $entry);

            return;
        }

        $entry->isAuthor($user) ? $entry->softDelete() : $entry->trash();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryDeletedEvent($entry, $user));
    }

    public function purge(User $user, Entry $entry): void
    {
        $this->dispatcher->dispatch(new EntryBeforePurgeEvent($entry));

        $entry->magazine->removeEntry($entry);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();
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
            $entry->url    = ($this->urlCleaner)($dto->url);
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
}
