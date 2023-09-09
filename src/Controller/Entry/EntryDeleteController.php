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

class EntryDeleteController extends AbstractController
{
    public function __construct(
        private readonly EntryManager $manager,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'entry')]
    public function delete(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_delete', $request->request->get('token'));

        $this->manager->delete($this->getUserOrThrow(), $entry);

        $this->addFlash(
            'danger',
            'flash_thread_delete_success'
        );

        return $this->redirectToMagazine($magazine);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'entry')]
    public function restore(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_restore', $request->request->get('token'));

        $this->manager->restore($this->getUserOrThrow(), $entry);

        return $this->redirectToMagazine($magazine);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'entry')]
    public function purge(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_purge', $request->request->get('token'));

        $this->manager->purge($this->getUserOrThrow(), $entry);

        return $this->redirectToRefererOrHome($request);
    }
}
