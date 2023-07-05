<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Service\EntryManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryPinController extends AbstractController
{
    public function __construct(
        private readonly EntryManager $manager,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['entry_id' => 'id'])]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_pin', $request->request->get('token'));

        $entry = $this->manager->pin($entry);

        $this->addFlash(
            'success',
            $entry->sticky ? 'flash_thread_pin_success' : 'flash_thread_unpin_success'
        );

        return $this->redirectToRefererOrHome($request);
    }
}
