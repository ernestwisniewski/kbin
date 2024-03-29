<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api;

use App\ApiDataProvider\DtoPaginator;
use App\Controller\AbstractController;
use App\Entity\Post;
use App\Kbin\PostComment\Factory\PostCommentFactory;
use App\Kbin\PostComment\PostCommentPageView;
use App\Repository\PostCommentRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class PostComments extends AbstractController
{
    public function __construct(
        private readonly PostCommentRepository $repository,
        private readonly PostCommentFactory $factory,
        private readonly RequestStack $request
    ) {
    }

    public function __invoke(Post $post)
    {
        try {
            $criteria = new PostCommentPageView((int) $this->request->getCurrentRequest()->get('p', 1));
            $criteria->post = $post;
            $criteria->onlyParents = false;

            $comments = $this->repository->findByCriteria($criteria);
        } catch (\Exception $e) {
            return [];
        }

        $dtos = array_map(fn ($comment) => $this->factory->createDto($comment),
            (array) $comments->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, PostCommentRepository::PER_PAGE, $comments->getNbResults());
    }
}
