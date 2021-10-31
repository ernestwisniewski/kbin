<?php declare(strict_types = 1);

namespace App\Controller;

use App\Repository\MagazineLogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModlogController extends AbstractController
{
    public function __invoke(MagazineLogRepository $repository, Request $request): Response
    {
        return $this->render(
            'modlog/modlog.html.twig',
            [
                'logs' => $repository->listAll($this->getPageNb($request)),
            ]
        );
    }
}
