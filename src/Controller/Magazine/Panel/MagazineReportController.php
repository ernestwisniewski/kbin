<?php declare(strict_types = 1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Report;
use App\Factory\ContentManagerFactory;
use App\Repository\MagazineRepository;
use App\Service\ReportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineReportController extends AbstractController
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
                'reports' => $reports,
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

        $manager->delete($this->getUserOrThrow(), $report->getSubject());

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
