<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\DTO\ModeratorDto;
use App\Entity\User;
use App\Form\ModeratorType;
use App\Repository\UserRepository;
use App\Service\InstanceManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminModeratorController extends AbstractController
{
    public function __construct(
        private readonly InstanceManager $manager,
        private readonly UserRepository $repository,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function moderators(Request $request): Response
    {
        $dto = new ModeratorDto(null);

        $form = $this->createForm(ModeratorType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->addModerator($dto);
        }

        $moderators = $this->repository->findModerators($this->getPageNb($request));

        return $this->render(
            'admin/moderators.html.twig',
            [
                'moderators' => $moderators,
                'form' => $form->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    public function removeModerator(User $user, Request $request): Response
    {
        $this->validateCsrf('remove_moderator', $request->request->get('token'));

        $this->manager->removeModerator($user);

        return $this->redirectToRefererOrHome($request);
    }
}
