<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __invoke(AuthenticationUtils $utils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_subscribed');
        }

        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        return $this->render('user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
}
