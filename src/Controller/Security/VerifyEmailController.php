<?php declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use App\Repository\UserRepository;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class VerifyEmailController extends AbstractController
{
    public function __invoke(Request $request, UserRepository $repository, UserManager $manager): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $repository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $manager->verify($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('app_login');
    }
}
