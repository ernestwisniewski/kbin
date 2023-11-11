<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\ActivityPubManager;
use App\Service\UserManager;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserApRefresh extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly ActivityPubManager $activityPubManager
    ) {
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    public function __invoke(User $user, Request $request): Response
    {
        $this->validateCsrf('user_ap_refresh', $request->request->get('token'));

        $this->userManager->detachCover($user);
        $this->userManager->detachAvatar($user);

        $this->activityPubManager->updateUser($user->apProfileId);

        return $this->redirectToRefererOrHome($request);
    }
}
