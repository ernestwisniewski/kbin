<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    public function front(): Response
    {
        return $this->render('front/front.html.twig');
    }
}
