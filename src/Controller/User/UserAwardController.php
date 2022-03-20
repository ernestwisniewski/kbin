<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAwardController extends AbstractController
{
    public function __invoke(User $user, ?string $category, Request $request): Response
    {
        return $this->render(
            'award/list_all.html.twig',
            [
                'user' => $user,
                'category' => $category,
                'types' => [],
            ]
        );
    }
}
