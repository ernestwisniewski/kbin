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

class MagazinePanelReportController extends AbstractController
{
    public function __construct(
        private MagazineRepository $repository,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function reports(Magazine $magazine, Request $request): Response
    {
        $reports = $this->repository->findReports($magazine, $this->getPageNb($request));

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
    public function reportReject(Magazine $magazine, Report $report, ReportManager $manager, Request $request): Response
    {
        $this->validateCsrf('report_decline', $request->request->get('token'));

        $manager->reject($report);

        return $this->redirectToRefererOrHome($request);
    }
}
