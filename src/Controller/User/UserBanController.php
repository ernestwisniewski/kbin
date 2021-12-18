<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserBanController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function ban(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_ban', $request->request->get('token'));

        $manager->ban($user);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBanned' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function unban(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_ban', $request->request->get('token'));

        $manager->unban($user);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBanned' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
