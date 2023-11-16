<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Kbin\Magazine\MagazineBlock;
use App\Kbin\Magazine\MagazineUnblock;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineBlockController extends AbstractController
{
    public function __construct(
        private readonly MagazineBlock $magazineBlock,
        private readonly MagazineUnblock $magazineUnblock,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('block', subject: 'magazine')]
    public function block(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        ($this->magazineBlock)($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($magazine);
        }

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('block', subject: 'magazine')]
    public function unblock(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        ($this->magazineUnblock)($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($magazine);
        }

        return $this->redirectToRefererOrHome($request);
    }

    private function getJsonResponse(Magazine $magazine): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'components/_ajax.html.twig',
                    [
                        'component' => 'magazine_sub',
                        'attributes' => [
                            'magazine' => $magazine,
                        ],
                    ]
                ),
            ]
        );
    }
}
