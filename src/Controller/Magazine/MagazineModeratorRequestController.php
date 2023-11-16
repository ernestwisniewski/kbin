<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Kbin\Magazine\ModeratorRequest\MagazineToggleModeratorRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineModeratorRequestController extends AbstractController
{
    public function __construct(private readonly MagazineToggleModeratorRequest $magazineToggleModeratorRequest)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('subscribe', subject: 'magazine')]
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('moderator_request', $request->request->get('token'));

        ($this->magazineToggleModeratorRequest)($magazine, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }
}
