<?php declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GithubController extends AbstractController
{
    public function connect(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('github')
            ->redirect([
                'read:user',
                'user:email',
            ]);
    }

    public function verify(Request $request, ClientRegistry $client)
    {
    }
}
