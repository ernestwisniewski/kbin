<?php

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Report;
use App\Repository\MagazineRepository;
use App\Service\ReportManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineReportController extends AbstractController
{
    public function __construct(
        private readonly MagazineRepository $repository,
        private readonly ReportManager $reportManager
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function reports(Magazine $magazine, Request $request, string $status): Response
    {
        $reports = $this->repository->findReports($magazine, $this->getPageNb($request), status: $status);

        return $this->render(
            'magazine/panel/reports.html.twig',
            [
                'reports' => $reports,
                'magazine' => $magazine,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function reportApprove(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'report_id')]
        Report $report,
        Request $request
    ): Response {
        $this->validateCsrf('report_approve', $request->request->get('token'));

        $this->reportManager->accept($report, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function reportReject(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'report_id')]
        Report $report,
        Request $request
    ): Response {
        $this->validateCsrf('report_decline', $request->request->get('token'));

        $this->reportManager->reject($report, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }
}
