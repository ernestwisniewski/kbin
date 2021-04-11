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

class MagazinePanelModController extends AbstractController
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
    public function deleteModerator(Magazine $magazine, Moderator $moderator, Request $request): Response
    {
        $this->validateCsrf('remove_moderator', $request->request->get('token'));

        $this->manager->removeModerator($moderator);

        return $this->redirectToRefererOrHome($request);
    }

}
