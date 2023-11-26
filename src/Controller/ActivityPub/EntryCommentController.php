<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Factory\ActivityPub\EntryCommentNoteFactory;
use App\Kbin\SpamProtection\Exception\SpamProtectionVerificationFailed;
use App\Kbin\SpamProtection\SpamProtectionCheck;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EntryCommentController extends AbstractController
{
    use PrivateContentTrait;

    public function __construct(
        private readonly EntryCommentNoteFactory $commentNoteFactory,
        private readonly SpamProtectionCheck $spamProtectionCheck
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request
    ): Response {
        if ($comment->apId) {
            return $this->redirect($comment->apId);
        }

        try {
            ($this->spamProtectionCheck)($comment->user);
        } catch (SpamProtectionVerificationFailed $e) {
            throw new AccessDeniedHttpException();
        }

        $this->handlePrivateContent($comment);

        $response = new JsonResponse($this->commentNoteFactory->create($comment, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
