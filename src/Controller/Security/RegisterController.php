<?php declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use App\Form\UserRegisterType;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends AbstractController
{
    public function __invoke(
        UserManager $manager,
        Request $request
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_subscribed');
        }

        $form = $this->createForm(UserRegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto     = $form->getData();
            $dto->ip = $request->getClientIp();

            $manager->create($dto);

            return $this->redirectToRoute('front');
        }

        return $this->render(
            'user/register.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
