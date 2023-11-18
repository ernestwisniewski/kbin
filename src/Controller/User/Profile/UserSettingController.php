<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Kbin\User\Form\UserSettingsType;
use App\Service\UserSettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSettingController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    public function __invoke(UserSettingsManager $manager, Request $request): Response
    {
        $dto = $manager->createDto($this->getUserOrThrow());

        $form = $this->createForm(UserSettingsType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->update($this->getUserOrThrow(), $dto);

            $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'user/settings/general.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
