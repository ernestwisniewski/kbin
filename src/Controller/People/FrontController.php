<?php declare(strict_types=1);

namespace App\Controller\People;

use App\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(?string $category, Request $request): Response
    {
        return $this->render(
            'people/front.html.twig',
            [
                'local' => $this->userRepository->findWithAbout(UserRepository::USERS_LOCAL),
                'federated' => $this->userRepository->findWithAbout(UserRepository::USERS_REMOTE),
            ]
        );
    }
}
