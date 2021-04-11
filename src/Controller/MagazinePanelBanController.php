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

class MagazinePanelBanController extends AbstractController
{
    public function __construct(
        private MagazineManager $manager,
        private MagazineRepository $repository,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function bans(Magazine $magazine, UserRepository $repository, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $user = $repository->findOneByUsername($request->get('user'));

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

        $bans = $this->repository->findBans($magazine, $this->getPageNb($request));

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
            $this->manager->ban($magazine, $user, $this->getUserOrThrow(), $magazineBanDto);

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

        $this->manager->unban($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }
}
