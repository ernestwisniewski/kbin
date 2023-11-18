<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Kbin\EntryComment\EntryCommentDelete;
use App\Kbin\EntryComment\EntryCommentPurge;
use App\Kbin\EntryComment\EntryCommentRestore;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentDeleteController extends AbstractController
{
    public function __construct(
        private readonly EntryCommentDelete $entryCommentDelete,
        private readonly EntryCommentRestore $entryCommentRestore,
        private readonly EntryCommentPurge $entryCommentPurge,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'comment')]
    public function delete(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('entry_comment_delete', $request->request->get('token'));

        ($this->entryCommentDelete)($this->getUserOrThrow(), $comment);

        return $this->redirectToEntry($entry);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'comment')]
    public function restore(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('entry_comment_restore', $request->request->get('token'));

        ($this->entryCommentRestore)($this->getUserOrThrow(), $comment);

        return $this->redirectToEntry($entry);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'comment')]
    public function purge(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('entry_comment_purge', $request->request->get('token'));

        ($this->entryCommentPurge)($this->getUserOrThrow(), $comment);

        return $this->redirectToRefererOrHome($request);
    }
}
