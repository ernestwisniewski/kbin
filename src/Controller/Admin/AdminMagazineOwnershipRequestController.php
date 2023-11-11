<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\User;
use App\Repository\MagazineOwnershipRequestRepository;
use App\Service\MagazineManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminMagazineOwnershipRequestController extends AbstractController
{
    public function __construct(
        private readonly MagazineOwnershipRequestRepository $repository,
        private readonly MagazineManager $manager
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

        $this->manager->acceptOwnershipRequest($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function reject(Magazine $magazine, User $user, Request $request): Response
    {
        $this->validateCsrf('admin_magazine_ownership_requests_reject', $request->request->get('token'));

        $this->manager->toggleOwnershipRequest($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }
}
