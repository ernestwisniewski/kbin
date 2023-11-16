<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Kbin\Entry\EntryChangeMagazine;
use App\Repository\MagazineRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryChangeMagazineController extends AbstractController
{
    public function __construct(
        private readonly EntryChangeMagazine $entryChangeMagazine,
        private readonly MagazineRepository $repository
    ) {
    }

    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('change_magazine', $request->request->get('token'));

        $newMagazine = $this->repository->findOneByName($request->get('change_magazine')['new_magazine']);

        ($this->entryChangeMagazine)($entry, $newMagazine);

        return $this->redirectToRefererOrHome($request);
    }
}
