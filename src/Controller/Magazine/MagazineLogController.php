<?php declare(strict_types = 1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineLogController extends AbstractController
{
    public function __invoke(Magazine $magazine, MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'magazine/modlog.html.twig',
            [
                'magazine' => $magazine,
                'logs'     => $repository->findModlog($magazine, $this->getPageNb($request)),
            ]
        );
    }
}
