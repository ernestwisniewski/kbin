<?php declare(strict_types=1);

namespace App\Controller;

use BadMethodCallException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use function is_string;

/**
 * @method User|null getUser()
 */
abstract class AbstractController extends BaseAbstractController
{
    protected function getUserOrThrow(): User
    {
        $user = $this->getUser();

        if (!$user) {
            throw new BadMethodCallException('User is not logged in');
        }

        return $user;
    }

    protected function validateCsrf(string $id, $token): void
    {
        if (!is_string($token) || !$this->isCsrfTokenValid($id, $token)) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }
    }

    protected function redirectToRefererOrHome(Request $request): Response
    {
        if (!$request->headers->has('Referer')) {
            return $this->redirectToRoute('front');
        }

        return $this->redirect($request->headers->get('Referer'));
    }
}
