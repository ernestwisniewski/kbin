<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts\FavouriteInterface;
use App\Kbin\Factory\HtmlClassFactory;
use App\Kbin\Favourite\FavouriteToggle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FavouriteController extends AbstractController
{
    public function __construct(
        private readonly HtmlClassFactory $classService,
        private readonly FavouriteToggle $favouriteToggle
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(FavouriteInterface $subject, Request $request): Response
    {
        $this->validateCsrf('favourite', $request->request->get('token'));

        ($this->favouriteToggle)($this->getUserOrThrow(), $subject);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView('components/_ajax.html.twig', [
                            'component' => 'vote',
                            'attributes' => [
                                'subject' => $subject,
                                'showDownvote' => str_contains(\get_class($subject), 'Entry'),
                            ],
                        ]
                    ),
                ]
            );
        }

        return $this->redirectToRefererOrHome($request, $this->classService->fromEntity($subject));
    }
}
