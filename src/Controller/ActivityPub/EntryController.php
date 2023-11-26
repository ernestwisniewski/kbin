<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Factory\ActivityPub\EntryPageFactory;
use App\Kbin\SpamProtection\Exception\SpamProtectionVerificationFailed;
use App\Kbin\SpamProtection\SpamProtectionCheck;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EntryController extends AbstractController
{
    public function __construct(
        private readonly EntryPageFactory $pageFactory,
        private readonly SpamProtectionCheck $spamProtectionCheck
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        if ($entry->apId) {
            return $this->redirect($entry->apId);
        }

        try {
            ($this->spamProtectionCheck)($entry->user);
        } catch (SpamProtectionVerificationFailed $e) {
            throw new AccessDeniedHttpException();
        }

        $response = new JsonResponse($this->pageFactory->create($entry, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
