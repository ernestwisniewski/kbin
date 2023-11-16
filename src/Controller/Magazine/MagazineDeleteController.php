<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Kbin\Magazine\MagazinePurge;
use App\Kbin\Magazine\MagazineRestore;
use App\Kbin\Magazine\MagazineTrash;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineDeleteController extends AbstractController
{
    public function __construct(
        private readonly MagazineTrash $magazineTrash,
        private readonly MagazineRestore $magazineRestore,
        private readonly MagazinePurge $magazinePurge,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'magazine')]
    public function delete(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_delete', $request->request->get('token'));

        ($this->magazineTrash)($magazine);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'magazine')]
    public function restore(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_restore', $request->request->get('token'));

        ($this->magazineRestore)($magazine);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'magazine')]
    public function purge(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_purge', $request->request->get('token'));

        ($this->magazinePurge)($magazine);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'magazine')]
    public function purgeContent(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_purge_content', $request->request->get('token'));

        ($this->magazinePurge)($magazine, true);

        return $this->redirectToRefererOrHome($request);
    }
}
