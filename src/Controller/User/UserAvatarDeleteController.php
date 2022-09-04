<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAvatarDeleteController extends AbstractController
{
    public function __construct(private UserManager $userManager)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $this->userManager->detachAvatar($this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'success' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
