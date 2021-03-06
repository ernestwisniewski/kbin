<?php

namespace App\Controller;

use App\Entity\Contracts\ReportInterface;
use App\Service\ReportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    private ReportManager $reportManager;

    public function __construct(ReportManager $reportManager)
    {
        $this->reportManager = $reportManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(ReportInterface $report, Request $request): Response
    {
        $this->validateCsrf('vote', $request->request->get('token'));

        $this->reportManager->report();

        return new Response('');
    }
}
