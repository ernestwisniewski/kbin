<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\InstanceStatsManager;

class DashboardController extends AbstractController
{
    public function __construct(private InstanceStatsManager $counter)
    {
    }

    public function __invoke()
    {
       return $this->render('admin/dashboard.html.twig', $this->counter->count('-24 hours'));
    }
}
