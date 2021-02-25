<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\EntryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Entry;
use App\Utils\Embed;

class AjaxController extends AbstractController
{
    public function fetchTitle(Embed $embed, Request $request): JsonResponse
    {
        $url = json_decode($request->getContent())->url;

        return new JsonResponse(
            [
                'title' => $embed->fetch($url)->getTitle(),
            ]
        );
    }

    public function fetchEmbed(Entry $entry, EntryRepository $entryRepository, Embed $embed, Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $embed->fetch($entry->getUrl())->getHtml(),
            ]
        );
    }
}
