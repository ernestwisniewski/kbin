<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Service\InstanceStatsManager;
use App\Service\SettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AboutController extends AbstractController
{
    public function __invoke(SettingsManager $settings, SiteRepository $repository, InstanceStatsManager $counter, Request $request): Response
    {
        $site = $repository->findAll();

        return $this->render(
            'page/about.html.twig',
            [
                'body' => $site[0]->about ?? '',
            ]
        );
    }
}
