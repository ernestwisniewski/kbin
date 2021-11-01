<?php declare(strict_types = 1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserDeleteController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_delete', $request->request->get('token'));

        $manager->delete($user);

        return $this->redirectToRoute('front');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function purge(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_purge', $request->request->get('token'));

        $manager->delete($user, true);

        return $this->redirectToRoute('front');
    }
}
