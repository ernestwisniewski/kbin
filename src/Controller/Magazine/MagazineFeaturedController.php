<?php declare(strict_types = 1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Response;

class MagazineFeaturedController extends AbstractController
{
    public function __invoke(?Magazine $magazine, MagazineRepository $repository): Response
    {
        $magazines = $repository->findBy([], null, 45);

        if ($magazine && !in_array($magazine, $magazines)) {
            array_unshift($magazines, $magazine);
        }

        return $this->render(
            'magazine/_featured.html.twig',
            [
                'magazine' => $magazine,
                'magazines' => $magazines,
            ]
        );
    }
}
