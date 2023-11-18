<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Kbin\Post\PostImageDetach;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostDeleteImageController extends AbstractController
{
    public function __construct(private readonly PostImageDetach $postImageDetach)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'post')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        ($this->postImageDetach)($post);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'success' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
