<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Kbin\Entry\EntryPin;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryPinController extends AbstractController
{
    public function __construct(
        private readonly EntryPin $entryPin,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_pin', $request->request->get('token'));

        $entry = ($this->entryPin)($entry);

        $this->addFlash(
            'success',
            $entry->sticky ? 'flash_thread_pin_success' : 'flash_thread_unpin_success'
        );

        return $this->redirectToRefererOrHome($request);
    }
}
