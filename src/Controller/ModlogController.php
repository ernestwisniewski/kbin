<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\MagazineLog;
use App\Repository\MagazineLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModlogController extends AbstractController
{
    public function __invoke(MagazineLogRepository $magazineLogRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'modlog/modlog.html.twig',
            [
                'logs' => $magazineLogRepository->listAll((int) $request->get('strona', $page)),
            ]
        );
    }
}
