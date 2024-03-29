<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts\VotableInterface;
use App\Kbin\Factory\HtmlClassFactory;
use App\Kbin\Vote\VoteCreate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BoostController extends AbstractController
{
    public function __construct(
        private readonly VoteCreate $voteCreate,
        private readonly HtmlClassFactory $classService
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(VotableInterface $subject, Request $request): Response
    {
        $this->validateCsrf('boost', $request->request->get('token'));

        ($this->voteCreate)(VotableInterface::VOTE_UP, $subject, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'components/_ajax.html.twig',
                        [
                            'component' => 'boost',
                            'attributes' => [
                                'subject' => $subject,
                                'path' => $request->attributes->get('_route'),
                            ],
                        ]
                    ),
                ]
            );
        }

        return $this->redirectToRefererOrHome($request, $this->classService->fromEntity($subject));
    }
}
