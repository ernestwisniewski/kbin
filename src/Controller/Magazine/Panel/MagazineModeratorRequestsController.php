<?php

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\User;
use App\Repository\ModeratorRequestRepository;
use App\Service\MagazineManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineModeratorRequestsController extends AbstractController
{
    public function __construct(
        private readonly MagazineManager $manager,
        private readonly ModeratorRequestRepository $repository,
    ) {
    }

    #[IsGranted('edit', subject: 'magazine')]
    public function requests(Magazine $magazine, Request $request): Response
    {
        return $this->render('magazine/panel/moderator_requests.html.twig', [
            'magazine' => $magazine,
            'requests' => $this->repository->findAllPaginated($magazine, $request->get('page', 1)),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'magazine')]
    public function accept(Magazine $magazine, User $user, Request $request): Response
    {
        $this->validateCsrf('magazine_panel_moderator_request_accept', $request->request->get('token'));

        $this->manager->acceptModeratorRequest($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'magazine')]
    public function reject(Magazine $magazine, User $user, Request $request): Response
    {
        $this->validateCsrf('magazine_panel_moderator_request_reject', $request->request->get('token'));

        $this->manager->toggleModeratorRequest($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }
}
