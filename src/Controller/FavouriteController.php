<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts\FavouriteInterface;
use App\Service\FavouriteManager;
use App\Service\GenerateHtmlClassService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FavouriteController extends AbstractController
{
    public function __construct(private readonly GenerateHtmlClassService $classService)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(FavouriteInterface $subject, Request $request, FavouriteManager $manager): Response
    {
        $this->validateCsrf('favourite', $request->request->get('token'));

        $manager->toggle($this->getUserOrThrow(), $subject);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView('components/_ajax.html.twig', [
                            'component' => 'vote',
                            'attributes' => [
                                'subject' => $subject,
                                'showDownvote' => str_contains(get_class($subject), 'Entry'),
                            ],
                        ]
                    ),
                ]
            );
        }

        return $this->redirectToRefererOrHome($request, ($this->classService)->fromEntity($subject));
    }
}
