<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Tag;

use App\Controller\AbstractController;
use App\Kbin\SubjectOverviewListCreate;
use App\Repository\TagRepository;
use App\Service\TagManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagOverviewController extends AbstractController
{
    public function __construct(
        private readonly TagManager $tagManager,
        private readonly TagRepository $tagRepository,
        private readonly SubjectOverviewListCreate $subjectOverviewListCreate
    ) {
    }

    public function __invoke(string $name, Request $request): Response
    {
        $activity = $this->tagRepository->findOverall(
            $this->getPageNb($request),
            $this->tagManager->transliterate(strtolower($name))
        );

        return $this->render(
            'tag/overview.html.twig',
            [
                'tag' => $name,
                'results' => ($this->subjectOverviewListCreate)($activity),
                'pagination' => $activity,
            ]
        );
    }
}
