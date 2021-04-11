<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UserProfileSettingsManager;
use App\Repository\NotificationRepository;
use App\Repository\MagazineRepository;
use App\Form\UserProfileSettingsType;
use App\Service\NotificationManager;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\UserRepository;

class ProfileController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function front(ChartBuilderInterface $chartBuilder): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        return $this->getChartsResponse($chartBuilder);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function notifications(NotificationRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/profile/notifications.html.twig',
            [
                'notifications' => $repository->findByUser($this->getUserOrThrow(), $this->getPageNb($request)),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function readNotifications(NotificationManager $manager, Request $request): Response
    {
        $this->validateCsrf('read_notifications', $request->request->get('token'));

        $manager->markAllAsRead($this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function clearNotifications(NotificationManager $manager, Request $request): Response
    {
        $this->validateCsrf('clear_notifications', $request->request->get('token'));

        $manager->clear($this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function settings(UserProfileSettingsManager $manager, Request $request): Response
    {
        $dto = $manager->createDto($this->getUserOrThrow());

        $form = $this->createForm(UserProfileSettingsType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->update($this->getUserOrThrow(), $dto);

            $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'user/profile/settings.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subMagazines(MagazineRepository $repository, Request $request): Response
    {
        $page = $this->getPageNb($request);

        return $this->render(
            'user/profile/sub_magazines.html.twig',
            [
                'magazines' => $repository->findSubscribedMagazines($page, $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subUsers(UserRepository $repository, Request $request): Response
    {
        $page = $this->getPageNb($request);

        return $this->render(
            'user/profile/sub_users.html.twig',
            [
                'users' => $repository->findFollowedUsers($page, $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function blockedMagazines(MagazineRepository $repository, Request $request): Response
    {
        $page = $this->getPageNb($request);

        return $this->render(
            'user/profile/block_magazines.html.twig',
            [
                'magazines' => $repository->findBlockedMagazines($page, $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function blockedUsers(UserRepository $repository, Request $request): Response
    {
        $page = $this->getPageNb($request);

        return $this->render(
            'user/profile/block_users.html.twig',
            [
                'users' => $repository->findBlockedUsers($page, $this->getUserOrThrow()),
            ]
        );
    }

    private function getChartsResponse(ChartBuilderInterface $chartBuilder)
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
            'user/profile/front.html.twig',
            [
                'contentChart' => $contentChart,
                'voteChart'    => $voteChart,
            ]
        );
    }
}
