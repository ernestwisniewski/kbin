<?php declare(strict_types=1);

namespace App\Service;

use App\Event\EntryDeletedEvent;
use App\Event\EntryPinEvent;
use App\Kernel;
use App\Service\Contracts\ContentManager;
use App\Utils\UrlCleaner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EntryRepository;
use App\Exception\BadUrlException;
use App\Event\EntryCreatedEvent;
use App\Event\EntryUpdatedEvent;
use App\Event\EntryBeforePurgeEvent;
use App\Factory\EntryFactory;
use Webmozart\Assert\Assert;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;

class EntryManager implements ContentManager
{
    public function __construct(
        private EntryFactory $entryFactory,
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
        private BadgeManager $badgeManager,
        private UrlCleaner $urlCleaner,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryDto $entryDto, User $user): Entry
    {
        // @todo
        if ($this->security->getUser() && !$this->security->isGranted('create_content', $entryDto->magazine)) {
            throw new AccessDeniedHttpException();
        }

        if ($entryDto->url) {
            $entryDto->url = ($this->urlCleaner)($entryDto->url);
            $this->validateUrl($entryDto->url);
        }

        $entry    = $this->entryFactory->createFromDto($entryDto, $user);
        $magazine = $entry->magazine;

        $this->assertType($entry);

        if ($entry->url) {
            $entry->type = Entry::ENTRY_TYPE_LINK;
        }

        if ($entryDto->badges) {
            $this->badgeManager->assign($entry, $entryDto->badges);
        }

        $magazine->addEntry($entry);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCreatedEvent($entry));

        return $entry;
    }

    public function edit(Entry $entry, EntryDto $entryDto): Entry
    {
        Assert::same($entry->magazine->getId(), $entryDto->magazine->getId());

        $entry->title   = $entryDto->title;
        $entry->url     = $entryDto->url;
        $entry->body    = $entryDto->body;
        $entry->isAdult = $entryDto->isAdult;

        if ($entryDto->image) {
            $entry->image = $entryDto->image;
        }

        if ($entry->url) {
            $entry->type = Entry::ENTRY_TYPE_LINK;
        }

        if ($entryDto->badges) {
            $this->badgeManager->assign($entry, $entryDto->badges);
        }

        $this->badgeManager->assign($entry, $entryDto->badges);

        $this->assertType($entry);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryUpdatedEvent($entry)));

        return $entry;
    }

    public function delete(Entry $entry, bool $trash = false): void
    {
        $trash ? $entry->trash() : $entry->softDelete();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryDeletedEvent($entry, $this->security->getUser())));
    }

    public function purge(Entry $entry): void
    {
        $this->eventDispatcher->dispatch((new EntryBeforePurgeEvent($entry)));

        $entry->magazine->removeEntry($entry);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }

    public function pin(Entry $entry): Entry
    {
        $entry->sticky = !$entry->sticky;

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryPinEvent($entry)));

        return $entry;
    }

    public function createDto(Entry $entry): EntryDto
    {
        return $this->entryFactory->createDto($entry);
    }

    private function assertType(Entry $entry): void
    {
        if ($entry->url) {
            Assert::null($entry->body);
        } else {
            Assert::null($entry->url);
        }
    }

    private function validateUrl(string $url): void
    {
        if (!$url) {
            return;
        }

        // @todo checkdnsrr?
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new BadUrlException($url);
        }
    }
}
