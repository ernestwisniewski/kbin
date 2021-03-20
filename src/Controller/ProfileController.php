<?php

namespace App\Controller;

use App\Repository\MagazineRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ProfileController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function front(ChartBuilderInterface $chartBuilder): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

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
            'profile/front.html.twig',
            [
                'contentChart' => $contentChart,
                'voteChart'    => $voteChart,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function notifications(NotificationRepository $notificationRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/notifications.twig',
            [
                'notifications' => $notificationRepository->findByUser($this->getUserOrThrow(), $page),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function messages(MessageRepository $messageRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/messages.twig',
            [
                'messages' => [],
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function settings(Request $request): Response
    {

        return $this->render(
            'profile/settings.twig',
            [
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subMagazines(MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/sub_magazines.twig',
            [
                'magazines' => $magazineRepository->findSubscribedMagazines($page, $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subUsers(UserRepository $userRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/sub_users.twig',
            [
                'users' => $userRepository->findFollowedUsers($page, $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function blockedMagazines(MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/block_magazines.twig',
            [
                'magazines' => $magazineRepository->findBlockedMagazines($page, $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function blockedUsers(UserRepository $userRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/block_users.twig',
            [
                'users' => $userRepository->findBlockedUsers($page, $this->getUserOrThrow()),
            ]
        );
    }
}
