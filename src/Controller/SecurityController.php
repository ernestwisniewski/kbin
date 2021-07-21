<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\UserDto;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\LoginAuthenticator;
use App\Service\UserManager;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{
    public function __construct()
    {
    }

    public function register(
        UserManager $manager,
        Request $request
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_subscribed');
        }

        $userDto = new UserDto();

        $form = $this->createForm(UserType::class, $userDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->create($userDto);

            return $this->redirectToRoute('front');
        }

        return $this->render(
            'user/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function verifyUserEmail(Request $request, UserRepository $repository, UserManager $manager): Response
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

    public function login(AuthenticationUtils $utils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_subscribed');
        }

        $error        = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        return $this->render('user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
