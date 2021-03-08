<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\MagazineBanDto;
use App\DTO\ModeratorDto;
use App\Entity\Moderator;
use App\Entity\Report;
use App\Entity\User;
use App\Form\MagazineBanType;
use App\Form\ModeratorType;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
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
                'front_magazine',
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
        $dto = new ModeratorDto($magazine);

        $form = $this->createForm(ModeratorType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magazineManager->addModerator($dto);
        }

        $moderators = $this->magazineRepository->findModeratorsPaginated($magazine, (int) $request->get('strona', 1));

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
    public function deleteModerator(Magazine $magazine, Moderator $moderator, Request $request): Response
    {
        $this->validateCsrf('remove_moderator', $request->request->get('token'));

        $this->magazineManager->removeModerator($moderator);

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function bans(Magazine $magazine, UserRepository $userRepository, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $user = $userRepository->findOneByUsername($request->get('user'));

            if (!$user) {
                return $this->redirectToRefererOrHome($request);
            }

            return $this->redirectToRoute(
                'magazine_panel_ban',
                [
                    'magazine_name' => $magazine->getName(),
                    'user_username' => $user->getUsername(),
                ]
            );
        }

        $bans = $this->magazineRepository->findBansPaginated($magazine, (int) $request->get('strona', 1));

        return $this->render(
            'magazine/panel/bans.html.twig',
            [
                'bans'     => $bans,
                'magazine' => $magazine,
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("user", options={"mapping": {"user_username": "username"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function ban(Magazine $magazine, User $user, Request $request): Response
    {
        $form = $this->createForm(MagazineBanType::class, $magazineBanDto = new MagazineBanDto());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magazineManager->ban($magazine, $user, $this->getUserOrThrow(), $magazineBanDto);

            return $this->redirectToRoute('magazine_panel_bans', ['name' => $magazine->getName()]);
        }

        return $this->render(
            'magazine/panel/ban.html.twig',
            [
                'magazine' => $magazine,
                'user'     => $user,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("user", options={"mapping": {"user_username": "username"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function unban(Magazine $magazine, User $user, Request $request): Response
    {
        $this->validateCsrf('magazine_unban', $request->request->get('token'));

        $this->magazineManager->unban($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function reports(Magazine $magazine, Request $request): Response
    {
        $reports = $this->magazineRepository->findReportsPaginated($magazine, (int) $request->get('strona', 1));

        return $this->render(
            'magazine/panel/reports.html.twig',
            [
                'reports'  => $reports,
                'magazine' => $magazine,
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function reportDecline(Magazine $magazine, Report $report, Request $request): Response
    {
        $this->validateCsrf('report_decline', $request->request->get('token'));
        dd($report);
    }
}
