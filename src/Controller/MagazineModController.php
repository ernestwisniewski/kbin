<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineModController extends AbstractController
{
    public function __invoke(Magazine $magazine, MagazineRepository $repository, Request $request): Response
    {
        $page = $this->getPageNb($request);

        return $this->render(
            'magazine/moderators.html.twig',
            [
                'magazine'   => $magazine,
                'moderators' => $repository->findModerators($magazine, (int) $request->get('strona', $page)),
            ]
        );
    }
}
