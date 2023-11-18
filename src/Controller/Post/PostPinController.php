<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Kbin\Post\PostPin;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostPinController extends AbstractController
{
    public function __construct(
        private readonly PostPin $postPin,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'post')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_pin', $request->request->get('token'));

        $entry = ($this->postPin)($post);

        $this->addFlash(
            'success',
            $entry->sticky ? 'flash_post_pin_success' : 'flash_post_unpin_success'
        );

        return $this->redirectToRefererOrHome($request);
    }
}
