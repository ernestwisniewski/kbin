<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Kbin\Post\PostDelete;
use App\Kbin\Post\PostPurge;
use App\Kbin\Post\PostRestore;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostDeleteController extends AbstractController
{
    public function __construct(
        private readonly PostDelete $postDelete,
        private readonly PostRestore $postRestore,
        private readonly PostPurge $postPurge
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'post')]
    public function delete(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_delete', $request->request->get('token'));

        ($this->postDelete)($this->getUserOrThrow(), $post);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'post')]
    public function restore(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_restore', $request->request->get('token'));

        ($this->postRestore)($this->getUserOrThrow(), $post);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'post')]
    public function purge(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_purge', $request->request->get('token'));

        ($this->postPurge)($this->getUserOrThrow(), $post);

        return $this->redirectToMagazine($magazine);
    }
}
