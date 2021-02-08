<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Embed;

class AjaxController extends AbstractController
{
    public function fetchTitle(Embed $embed, Request $request): JsonResponse
    {
        $url  = json_decode($request->getContent())->url;

        return new JsonResponse(
            [
                'title' => $embed->fetch($url)->getTitle(),
            ]
        );
    }
}
