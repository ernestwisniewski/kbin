<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Kbin\Entry\EntryMarkAsAdult;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryChangeAdultController extends AbstractController
{
    public function __construct(private readonly EntryMarkAsAdult $entryMarkAsAdult)
    {
    }

    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('change_adult', $request->request->get('token'));

        ($this->entryMarkAsAdult)($entry, 'on' === $request->get('adult'));

        return $this->redirectToRefererOrHome($request);
    }
}
