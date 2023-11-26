<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Factory\ActivityPub\PostCommentNoteFactory;
use App\Kbin\SpamProtection\Exception\SpamProtectionVerificationFailed;
use App\Kbin\SpamProtection\SpamProtectionCheck;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PostCommentController extends AbstractController
{
    use PrivateContentTrait;

    public function __construct(
        private readonly PostCommentNoteFactory $commentNoteFactory,
        private readonly SpamProtectionCheck $spamProtectionCheck
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
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

        $this->handlePrivateContent($post);

        $response = new JsonResponse($this->commentNoteFactory->create($comment, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
