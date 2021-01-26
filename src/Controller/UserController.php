<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\Criteria;
use App\Entity\User;

class UserController extends AbstractController
{
    public function front(User $user, Request $request, UserRepository $userRepository): Response
    {
        return $this->render(
            'user/front.html.twig',
            [
                'user'    => $user,
                'entries' => $userRepository->findPublicActivity((int) $request->get('strona', 1), $user),
            ]
        );
    }
}
