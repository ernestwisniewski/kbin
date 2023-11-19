<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Domain;

use App\Entity\Contracts\DomainInterface;
use App\Entity\Domain;
use App\Repository\DomainRepository;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;

readonly class DomainExtract
{
    public function __construct(
        private DomainRepository $repository,
        private SettingsManager $settingsManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(DomainInterface $subject): DomainInterface
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
}
