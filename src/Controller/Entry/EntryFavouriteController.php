<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryFavouriteController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['entry_id' => 'id'])]
        Entry $entry,
        Request $request
    ): Response {
        return $this->render('entry/favourites.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'favourites' => $entry->favourites,
        ]);
    }
}
