<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Kbin\Post\PostMagazineChange;
use App\Repository\MagazineRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostChangeMagazineController extends AbstractController
{
    public function __construct(
        private readonly PostMagazineChange $postMagazineChange,
        private readonly MagazineRepository $repository
    ) {
    }

    #[IsGranted('moderate', 'post')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('change_magazine', $request->request->get('token'));

        $newMagazine = $this->repository->findOneByName($request->get('change_magazine')['new_magazine']);

        ($this->postMagazineChange)($post, $newMagazine);

        return $this->redirectToRefererOrHome($request);
    }
}
