<?php

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Report;
use App\Factory\ContentManagerFactory;
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
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
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

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function reportApprove(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['report_id' => 'id'])]
        Report $report,
        ContentManagerFactory $managerFactory,
        Request $request
    ): Response {
        $this->validateCsrf('report_approve', $request->request->get('token'));

        $manager = $managerFactory->createManager($report->getSubject());

        $manager->delete($this->getUserOrThrow(), $report->getSubject());

        return $this->redirectToRefererOrHome($request);
    }


    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function reportReject(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['report_id' => 'id'])]
        Report $report,
        ReportManager $manager,
        Request $request
    ): Response {
        $this->validateCsrf('report_decline', $request->request->get('token'));

        $manager->reject($report);

        return $this->redirectToRefererOrHome($request);
    }
}
