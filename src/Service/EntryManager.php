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
use App\Exception\BadUrlException;
use App\Factory\EntryFactory;
use App\Service\Contracts\ContentManager;
use App\Utils\UrlCleaner;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;
use Webmozart\Assert\Assert;

class EntryManager implements ContentManager
{
    public function __construct(
        private EntryFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private Security $security,
        private BadgeManager $badgeManager,
        private UrlCleaner $urlCleaner,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryDto $dto, User $user): Entry
    {
        // @todo
        if (!$this->security->isGranted('create_content', $dto->magazine)) {
            throw new AccessDeniedHttpException();
        }

        if ($dto->url) {
            $dto->url = ($this->urlCleaner)($dto->url);
            $this->validateUrl($dto->url);
        }

        $entry    = $this->factory->createFromDto($dto, $user);
        $magazine = $entry->magazine;
        $this->assertType($entry);

        if ($dto->image) {
            $entry->image = $dto->image;
        }

        if ($entry->url) {
            $entry->type = Entry::ENTRY_TYPE_LINK;
        }

        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }

        $magazine->addEntry($entry);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCreatedEvent($entry));

        return $entry;
    }

    private function validateUrl(string $url): void
    {
        // @todo checkdnsrr?
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new BadUrlException($url);
        }
    }

    private function assertType(Entry $entry): void
    {
        if ($entry->url) {
            Assert::null($entry->body);
        } else {
            Assert::null($entry->url);
        }
    }

    public function edit(Entry $entry, EntryDto $dto): Entry
    {
        Assert::same($entry->magazine->getId(), $dto->magazine->getId());

        $entry->title   = $dto->title;
        $entry->url     = $dto->url;
        $entry->body    = $dto->body;
        $entry->isAdult = $dto->isAdult;

        if ($dto->image) {
            $entry->image = $dto->image;
        }

        if ($entry->url) {
            $entry->type = Entry::ENTRY_TYPE_LINK;
        }

        if ($dto->badges) {
            $this->badgeManager->assign($entry, $dto->badges);
        }

        $this->badgeManager->assign($entry, $dto->badges);

        $this->assertType($entry);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryUpdatedEvent($entry));

        return $entry;
    }

    public function delete(User $user, Entry $entry): void
    {
        $entry->isAuthor($user) ? $entry->softDelete() : $entry->trash();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryDeletedEvent($entry, $user));
    }

    public function purge(Entry $entry): void
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
}
