<?php

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\DTO\ModeratorDto;
use App\Entity\Magazine;
use App\Entity\Moderator;
use App\Form\ModeratorType;
use App\Repository\MagazineRepository;
use App\Service\MagazineManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineModeratorController extends AbstractController
{
    public function __construct(
        private readonly MagazineManager $manager,
        private readonly MagazineRepository $repository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'magazine')]
    public function moderators(Magazine $magazine, Request $request): Response
    {
        $dto = new ModeratorDto($magazine);

        $form = $this->createForm(ModeratorType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->addModerator($dto);
        }

        $moderators = $this->repository->findModerators($magazine, $this->getPageNb($request));

        return $this->render(
            'magazine/panel/moderators.html.twig',
            [
                'moderators' => $moderators,
                'magazine' => $magazine,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'magazine')]
    public function remove(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'moderator_id')]
        Moderator $moderator,
        Request $request
    ): Response {
        $this->validateCsrf('remove_moderator', $request->request->get('token'));

        $this->manager->removeModerator($moderator);

        return $this->redirectToRefererOrHome($request);
    }
}
