<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CardanoController extends AbstractController
{
    protected function send(Response $response): Response
    {
        $response->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => true,
            'private'          => true,
            'proxy_revalidate' => true,
        ]);

        return $response;
    }
}
