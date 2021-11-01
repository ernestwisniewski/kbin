<?php declare(strict_types = 1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\DTO\ModeratorDto;
use App\Entity\Magazine;
use App\Entity\Moderator;
use App\Form\ModeratorType;
use App\Repository\MagazineRepository;
use App\Service\MagazineManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class MagazineModeratorController extends AbstractController
{
    public function __construct(
        private MagazineManager $manager,
        private MagazineRepository $repository,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
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
                'magazine'   => $magazine,
                'form'       => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("moderator", options={"mapping": {"moderator_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function remove(Magazine $magazine, Moderator $moderator, Request $request): Response
    {
        $this->validateCsrf('remove_moderator', $request->request->get('token'));

        $this->manager->removeModerator($moderator);

        return $this->redirectToRefererOrHome($request);
    }

}
