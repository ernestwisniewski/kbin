<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class UserController extends AbstractController
{
    public function __construct(private UserRepository $repository, private RequestStack $request)
    {
    }

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
