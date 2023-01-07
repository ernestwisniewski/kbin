<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\InstanceStatsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class DashboardController extends AbstractController
{
    public function __construct(private readonly InstanceStatsManager $counter)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke()
    {
        return $this->render('admin/dashboard.html.twig', $this->counter->count('-24 hours'));
    }
}
