<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Entry;
use App\Entity\Magazine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryVotersController extends AbstractController
{
    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('entry', options: ['mapping' => ['entry_id' => 'id']])]
    public function __invoke(string $type, Magazine $magazine, Entry $entry, Request $request): Response
    {
        $votes = $entry->votes->filter(
            fn($e) => $e->choice === ($type === 'up' ? VoteInterface::VOTE_UP : VoteInterface::VOTE_DOWN)
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('layout/_activity_list.html.twig', [
                    'list' => $votes,
                    'more' => null,
                ]),
            ]);
        }

        return $this->render('entry/voters.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'votes' => $votes,
        ]);
    }
}
