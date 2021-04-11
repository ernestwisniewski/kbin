<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UserManager;
use App\Entity\User;

class UserBlockController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function block(User $blocked, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $manager->block($this->getUserOrThrow(), $blocked);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function unblock(User $blocked, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $manager->unblock($this->getUserOrThrow(), $blocked);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
