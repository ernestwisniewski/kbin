<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Kbin\Entry\EntryDelete;
use App\Kbin\Entry\EntryPurge;
use App\Kbin\Entry\EntryRestore;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryDeleteController extends AbstractController
{
    public function __construct(
        private readonly EntryDelete $entryDelete,
        private readonly EntryPurge $entryPurge,
        private readonly EntryRestore $entryRestore
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'entry')]
    public function delete(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_delete', $request->request->get('token'));

        ($this->entryDelete)($this->getUserOrThrow(), $entry);

        $this->addFlash(
            'danger',
            'flash_thread_delete_success'
        );

        return $this->redirectToMagazine($magazine);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'entry')]
    public function restore(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_restore', $request->request->get('token'));

        ($this->entryRestore)($this->getUserOrThrow(), $entry);

        return $this->redirectToMagazine($magazine);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'entry')]
    public function purge(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_purge', $request->request->get('token'));

        ($this->entryPurge)($this->getUserOrThrow(), $entry);

        return $this->redirectToRefererOrHome($request);
    }
}
