<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazinePeopleFrontController extends AbstractController
{
    public function __construct(
        private readonly MagazineRepository $magazineRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    public function __invoke(
        Magazine $magazine,
        ?string $category,
        Request $request
    ): Response {
        return $this->render(
            'people/front.html.twig', [
                'magazine' => $magazine,
                'magazines' => array_filter(
                    $this->magazineRepository->findByActivity(),
                    fn ($val) => 'random' !== $val->name && $val !== $magazine
                ),
                'local' => $this->userRepository->findPeople($magazine),
                'federated' => $this->userRepository->findPeople($magazine, true),
            ]
        );
    }
}
