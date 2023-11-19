<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\ActivityPub;

use App\Entity\BrokenInstance;
use App\Repository\BrokenInstanceRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ActivityPubInstanceBrokenCreate
{
    public function __construct(
        //        private BrokenInstanceRepository $brokenInstanceRepository,
        //        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(string $url, string $exception = null): void
    {
        //        $url = str_replace('www.', '', parse_url($url, PHP_URL_HOST));
        //
        //        $entity = $this->brokenInstanceRepository->findOneByHost($url);
        //
        //        if (!$entity) {
        //            $entity = new BrokenInstance();
        //            $entity->host = $url;
        //            $entity->exception = $exception;
        //
        //            $this->entityManager->persist($entity);
        //            $this->entityManager->flush();
        //        }
    }
}
