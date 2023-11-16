<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\OwnershipRequest\MagazineOwnershipRequestAccept;
use App\Kbin\Magazine\OwnershipRequest\MagazineOwnershipRequestToggle;
use App\Repository\MagazineOwnershipRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminMagazineOwnershipRequestController extends AbstractController
{
    public function __construct(
        private readonly MagazineOwnershipRequestAccept $magazineOwnershipRequestAccept,
        private readonly MagazineOwnershipRequestToggle $magazineOwnershipRequestToggle,
        private readonly MagazineOwnershipRequestRepository $repository,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function requests(Request $request): Response
    {
        return $this->render('admin/magazine_ownership.html.twig', [
            'requests' => $this->repository->findAllPaginated($request->get('p', 1)),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function accept(Magazine $magazine, User $user, Request $request): Response
    {
        $this->validateCsrf('admin_magazine_ownership_requests_accept', $request->request->get('token'));

        ($this->magazineOwnershipRequestAccept)($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function reject(Magazine $magazine, User $user, Request $request): Response
    {
        $this->validateCsrf('admin_magazine_ownership_requests_reject', $request->request->get('token'));

        ($this->magazineOwnershipRequestToggle)($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }
}
