<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Factory\ActivityPub\PostNoteFactory;
use App\Kbin\SpamProtection\Exception\SpamProtectionVerificationFailed;
use App\Kbin\SpamProtection\SpamProtectionCheck;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PostController extends AbstractController
{
    public function __construct(
        private readonly PostNoteFactory $postNoteFactory,
        private readonly SpamProtectionCheck $spamProtectionCheck
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        if ($post->apId) {
            return $this->redirect($post->apId);
        }

        try {
            ($this->spamProtectionCheck)($post->user);
        } catch (SpamProtectionVerificationFailed $e) {
            throw new AccessDeniedHttpException();
        }

        $response = new JsonResponse($this->postNoteFactory->create($post, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
