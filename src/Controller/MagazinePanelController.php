<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\MagazineBanDto;
use App\Entity\User;
use App\Form\MagazineBanType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MagazineRepository;
use App\Repository\EntryRepository;
use Pagerfanta\PagerfantaInterface;
use App\Service\MagazineManager;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Form\MagazineType;
use App\Entity\Magazine;
use App\DTO\MagazineDto;

class MagazinePanelController extends AbstractController
{
    private MagazineManager $magazineManager;
    private MagazineRepository $magazineRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(MagazineManager $magazineManager, MagazineRepository $magazineRepository, EntityManagerInterface $entityManager)
    {
        $this->magazineManager    = $magazineManager;
        $this->magazineRepository = $magazineRepository;
        $this->entityManager      = $entityManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function edit(Magazine $magazine, Request $request): Response
    {
        $magazineDto = $this->magazineManager->createDto($magazine);

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magazineManager->edit($magazine, $magazineDto);

            return $this->redirectToRoute(
                'magazine',
                [
                    'name' => $magazine->getName(),
                ]
            );
        }

        return $this->render(
            'magazine/panel/edit.html.twig',
            [
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function moderators(Magazine $magazine, Request $request): Response
    {
        $moderators = $this->magazineRepository->findModeratorsPaginated($magazine, (int) $request->get('strona', 1));

        return $this->render(
            'magazine/panel/moderators.html.twig',
            [
                'moderators' => $moderators,
                'magazine'   => $magazine,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function bans(Magazine $magazine, Request $request): Response
    {
        $bans = $this->magazineRepository->getBansPaginated($magazine, (int) $request->get('strona', 1));

        return $this->render(
            'magazine/panel/bans.html.twig',
            [
                'bans'     => $bans,
                'magazine' => $magazine,
            ]
        );
    }
}
