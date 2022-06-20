<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Service\SettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivacyPolicyController extends AbstractController
{

    public function __invoke(SettingsManager $settings, SiteRepository $repository, Request $request): Response
    {
        $site = $repository->findOneBy(['domain' => $settings->get('KBIN_DOMAIN')]);

        return $this->render(
            'page/privacy_policy.html.twig',
            [
                'body' => $site ? $site->privacyPolicy : ''
            ]
        );
    }
}
