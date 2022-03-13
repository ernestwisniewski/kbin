<?php declare(strict_types=1);

namespace App\Controller\Award;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AwardListController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(Request $request): Response
    {
        return $this->render(
            'award/list_all.html.twig'
        );
    }
}
