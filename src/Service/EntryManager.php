<?php declare(strict_types=1);

namespace App\Service;

use App\Event\EntryDeletedEvent;
use App\Kernel;
use App\Service\Contracts\ContentManager;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\EntryCreatedMessage;
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
    private EntryFactory $entryFactory;
    private EntryRepository $entryRepository;
    private EventDispatcherInterface $eventDispatcher;
    private MessageBusInterface $messageBus;
    private Security $security;
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private Kernel $kernel;

    public function __construct(
        EntryFactory $entryFactory,
        EntryRepository $entryRepository,
        EventDispatcherInterface $eventDispatcher,
        MessageBusInterface $messageBus,
        Security $security,
        HttpClientInterface $client,
        Kernel $kernel,
        EntityManagerInterface $entityManager
    ) {
        $this->entryFactory    = $entryFactory;
        $this->entryRepository = $entryRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus      = $messageBus;
        $this->security        = $security;
        $this->client          = $client;
        $this->entityManager   = $entityManager;
        $this->kernel          = $kernel;
    }

    public function create(EntryDto $entryDto, User $user): Entry
    {
        // @todo
        if ($this->security->getUser() && !$this->security->isGranted('create_content', $entryDto->getMagazine())) {
            throw new AccessDeniedHttpException();
        }

        $this->validateUrl($entryDto->getUrl());

        $entry    = $this->entryFactory->createFromDto($entryDto, $user);
        $magazine = $entry->getMagazine();

        $this->assertType($entry);

        $magazine->addEntry($entry);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCreatedEvent($entry));
        $this->messageBus->dispatch(new EntryCreatedMessage($entry->getId()));

        return $entry;
    }

    public function edit(Entry $entry, EntryDto $entryDto): Entry
    {
        Assert::same($entry->getMagazine()->getId(), $entryDto->getMagazine()->getId());

        $entry->setTitle($entryDto->getTitle());
        $entry->setUrl($entryDto->getUrl());
        $entry->setBody($entryDto->getBody());
        if ($entryDto->getImage()) {
            $entry->setImage($entryDto->getImage());
        }

        $this->assertType($entry);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryUpdatedEvent($entry)));

        return $entry;
    }

    public function delete(Entry $entry, bool $trash = false): void
    {
        if ($entry->getCommentCount() >= 1) {
            $trash ? $entry->trash() : $entry->softDelete();
        } else {
            $this->purge($entry);

            return;
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryDeletedEvent($entry)));
    }

    public function purge(Entry $entry): void
    {
        $this->eventDispatcher->dispatch((new EntryBeforePurgeEvent($entry)));

        $entry->getMagazine()->removeEntry($entry);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }

    public function createDto(Entry $entry): EntryDto
    {
        return $this->entryFactory->createDto($entry);
    }

    private function assertType(Entry $entry): void
    {
        if ($entry->getUrl()) {
            Assert::null($entry->getBody());
        } else {
            Assert::null($entry->getUrl());
        }
    }

    private function validateUrl(?string $url): void
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
