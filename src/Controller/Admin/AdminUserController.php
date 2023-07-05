<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminUserController extends AbstractController
{
    public function __construct(private readonly UserRepository $repository, private readonly RequestStack $request)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke()
    {
        return $this->render(
            'admin/users.html.twig',
            [
                'users' => $this->repository->findAllPaginated(
                    (int) $this->request->getCurrentRequest()->get('p', 1)
                ),
            ]
        );
    }
}
