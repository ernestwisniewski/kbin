<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MagazineRepository;
use App\Factory\ContentManagerFactory;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\UserRepository;
use App\Service\MagazineManager;
use App\Form\MagazineThemeType;
use App\Service\ReportManager;
use App\Service\BadgeManager;
use App\DTO\MagazineThemeDto;
use App\Form\MagazineBanType;
use App\DTO\MagazineBanDto;
use App\Form\ModeratorType;
use App\Form\MagazineType;
use App\DTO\ModeratorDto;
use App\Entity\Moderator;
use App\Entity\Magazine;
use App\Form\BadgeType;
use App\DTO\BadgeDto;
use App\Entity\Badge;
use App\Entity\Report;
use App\Entity\User;

class MagazinePanelController extends AbstractController
{
    public function __construct(
        private MagazineManager $magazineManager,
        private MagazineRepository $magazineRepository,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function front(Magazine $magazine, ChartBuilderInterface $chartBuilder): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        return $this->getChartsResponse($magazine, $chartBuilder);
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

            return $this->redirectToMagazine($magazine);
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

        $moderators = $this->magazineRepository->findModerators($magazine, $this->getPageNb($request));

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
                    'magazine_name' => $magazine->name,
                    'user_username' => $user->getUsername(),
                ]
            );
        }

        $bans = $this->magazineRepository->findBans($magazine, $this->getPageNb($request));

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

            return $this->redirectToRoute('magazine_panel_bans', ['name' => $magazine->name]);
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
        $reports = $this->magazineRepository->findReports($magazine, $this->getPageNb($request));

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
    public function reportApprove(Magazine $magazine, Report $report, ContentManagerFactory $managerFactory, Request $request): Response
    {
        $this->validateCsrf('report_approve', $request->request->get('token'));

        $manager = $managerFactory->createManager($report->getSubject());

        $manager->delete($report->getSubject(), true);

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function reportReject(Magazine $magazine, Report $report, ReportManager $reportManager, Request $request): Response
    {
        $this->validateCsrf('report_decline', $request->request->get('token'));

        $reportManager->reject($report);

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function theme(Magazine $magazine, Request $request): Response
    {
        $dto = new MagazineThemeDto($magazine);

        $form = $this->createForm(MagazineThemeType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magazineManager->changeTheme($dto);
        }

        return $this->render(
            'magazine/panel/theme.html.twig',
            [
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function badges(Magazine $magazine, BadgeManager $badgeManager, Request $request): Response
    {
        $badges = $this->magazineRepository->findBadges($magazine);

        $dto = new BadgeDto();

        $form = $this->createForm(BadgeType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto->magazine = $magazine;
            $badgeManager->create($dto);

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'magazine/panel/badges.html.twig',
            [
                'badges'   => $badges,
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("badge", options={"mapping": {"badge_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function removeBadge(Magazine $magazine, Badge $badge, BadgeManager $badgeManager, Request $request): Response
    {
        $this->validateCsrf('badge_remove', $request->request->get('token'));

        $badgeManager->delete($badge);

        return $this->redirectToRefererOrHome($request);
    }

    private function getChartsResponse(Magazine $magazine, ChartBuilderInterface $chartBuilder): Response
    {
        $labels = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Listopad', 'Grudzień'];

        $contentChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $contentChart->setData(
            [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'       => 'Treści',
                        'borderColor' => '#4382AD',
                        'data'        => [0, 10, 5, 2, 20, 30, 35, 23, 30, 35, 40],
                    ],
                    [
                        'label'       => 'Komentarze',
                        'borderColor' => '#6253ac',
                        'data'        => [0, 6, 11, 22, 10, 15, 25, 23, 35, 20, 11],
                    ],
                    [
                        'label'       => 'Wpisy',
                        'borderColor' => '#ac5353',
                        'data'        => [5, 15, 3, 15, 15, 24, 35, 36, 17, 11, 4],
                    ],
                ],
            ]
        );
        $contentChart->setOptions(
            [
                'scales' => [
                    'yAxes' => [
                        ['ticks' => ['min' => 0, 'max' => 40]],
                    ],
                ],
            ]
        );

        $voteChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $voteChart->setData(
            [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'       => 'Głosy pozytywne',
                        'borderColor' => '#4e805d',
                        'data'        => [0, 4, 5, 6, 7, 31, 38, 30, 22, 11, 11],
                    ],
                    [
                        'label'       => 'Głosy negatywne',
                        'borderColor' => '#b0403d',
                        'data'        => [0, 1, 3, 1, 4, 11, 4, 5, 3, 1, 11],
                    ],
                ],
            ]
        );
        $voteChart->setOptions(
            [
                'scales' => [
                    'yAxes' => [
                        ['ticks' => ['min' => 0, 'max' => 40]],
                    ],
                ],
            ]
        );

        return $this->render(
            'magazine/panel/front.html.twig',
            [
                'contentChart' => $contentChart,
                'voteChart'    => $voteChart,
                'magazine'     => $magazine,
            ]
        );
    }
}
