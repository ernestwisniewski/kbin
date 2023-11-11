<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\DTO\InstancesDto;
use App\Form\InstancesType;
use App\Service\SettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminFederationController extends AbstractController
{
    public function __construct(public readonly SettingsManager $settingsManager)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request)
    {
        $dto = new InstancesDto($this->settingsManager->get('KBIN_BANNED_INSTANCES'));

        $form = $this->createForm(InstancesType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto->instances = array_map(
                fn (string $instance) => trim(str_replace('www.', '', $instance)),
                $dto->instances ?? [],
            );

            $this->settingsManager->set('KBIN_BANNED_INSTANCES', $dto->instances);

            return $this->redirectToRoute('admin_federation');
        }

        return $this->render(
            'admin/federation.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
