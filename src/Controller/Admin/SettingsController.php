<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\SettingsManager;

class SettingsController extends AbstractController
{
    public function __construct(private SettingsManager $manager)
    {
    }

    public function __invoke() {
        dd($this->manager->get('KBIN_DOMAIN'));
    }
}
