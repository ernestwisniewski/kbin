<?php declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Entity\User;

/**
 * @method User|null getUser()
 */
abstract class AbstractController extends BaseAbstractController {
    protected function getUserOrThrow(): User {
        $user = $this->getUser();

        if(!$user) {
            throw new \BadMethodCallException('User is not logged in');
        }

        return $user;
    }

    protected function validateCsrf(string $id, $token): void {
        if (!\is_string($token) || !$this->isCsrfTokenValid($id, $token)) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }
    }
}
