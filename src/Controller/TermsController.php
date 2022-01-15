<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TermsController extends AbstractController
{

    public function __invoke(Request $request): Response
    {
        return $this->render(
            'page/terms.html.twig'
        );
    }
}
