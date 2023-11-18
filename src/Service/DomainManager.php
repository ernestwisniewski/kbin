<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\DomainInterface;
use App\Entity\Domain;
use App\Entity\User;
use App\Event\DomainBlockedEvent;
use App\Event\DomainSubscribedEvent;
use App\Repository\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class DomainManager
{
    public function __construct(
        private readonly DomainRepository $repository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function extract(DomainInterface $subject): DomainInterface
    {
        $domainName = $subject->getUrl() ?? 'https://'.$this->settingsManager->get('KBIN_DOMAIN');

        $domainName = preg_replace('/^www\./i', '', parse_url($domainName)['host']);

        $domain = $this->repository->findOneByName($domainName);

        if (!$domain) {
            $domain = new Domain($subject, $domainName);
            $subject->domain = $domain;
            $this->entityManager->persist($domain);
        }

        $domain->addEntry($subject);
        $domain->updateCounts();

        $this->entityManager->flush();

        return $subject;
    }

    public function subscribe(Domain $domain, User $user): void
    {
        $user->unblockDomain($domain);

        $domain->subscribe($user);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new DomainSubscribedEvent($domain, $user));
    }

    public function block(Domain $domain, User $user): void
    {
        $this->unsubscribe($domain, $user);

        $user->blockDomain($domain);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new DomainBlockedEvent($domain, $user));
    }

    public function unsubscribe(Domain $domain, User $user): void
    {
        $domain->unsubscribe($user);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new DomainSubscribedEvent($domain, $user));
    }

    public function unblock(Domain $domain, User $user): void
    {
        $user->unblockDomain($domain);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new DomainBlockedEvent($domain, $user));
    }

    public static function shouldRatio(string $domain): bool
    {
        $domainsWithRatio = ['youtube.com', 'streamable.com', 'youtu.be', 'm.youtube.com'];

        return (bool) array_filter($domainsWithRatio, fn ($item) => str_contains($domain, $item));
    }
}
