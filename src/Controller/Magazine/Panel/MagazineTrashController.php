<?php

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use App\Service\BadgeManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineTrashController extends AbstractController
{
    public function __construct(private readonly MagazineRepository $repository)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function __invoke(Magazine $magazine, BadgeManager $manager, Request $request): Response
    {
        return $this->render(
            'magazine/panel/trash.html.twig',
            [
                'magazine' => $magazine,
                'results' => $this->repository->findTrashed($magazine, $this->getPageNb($request)),
            ]
        );
    }
}
