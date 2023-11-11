<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Service\ActivityPubManager;
use App\Service\MagazineManager;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineApRefresh extends AbstractController
{
    public function __construct(
        private readonly MagazineManager $magazineManager,
        private readonly ActivityPubManager $activityPubManager
    ) {
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_ap_refresh', $request->request->get('token'));

        $this->magazineManager->detachIcon($magazine);

        $this->activityPubManager->updateMagazine($magazine->apProfileId);

        return $this->redirectToRefererOrHome($request);
    }
}
