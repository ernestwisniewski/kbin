<?php

declare(strict_types=1);

namespace App\Controller\People;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use App\Service\PeopleManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PeopleFrontController extends AbstractController
{
    public function __construct(private readonly PeopleManager $manager, private readonly MagazineRepository $magazineRepository)
    {
    }

    public function __invoke(?string $category, Request $request): Response
    {
        return $this->render(
            'people/front.html.twig', [
                'magazines' => array_filter(
                    $this->magazineRepository->findByActivity(),
                    fn ($val) => 'random' !== $val->name
                ),
                'local' => $this->manager->general(),
                'federated' => $this->manager->general(true),
            ]
        );
    }
}
