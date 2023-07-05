<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\Magazine;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryVotersController extends AbstractController
{
    public function __invoke(
        string $type,
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['entry_id' => 'id'])]
        Entry $entry,
        Request $request
    ): Response {
        $votes = $entry->votes->filter(
            fn($e) => $e->choice === ('up' === $type ? VotableInterface::VOTE_UP : VotableInterface::VOTE_DOWN)
        );

        return $this->render('entry/voters.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'votes' => $votes,
        ]);
    }
}
