<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\SiteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivacyPolicyController extends AbstractController
{

    public function __invoke(string $kbinDomain, SiteRepository $repository, Request $request): Response
    {
        $site = $repository->findOneBy(['domain' => $kbinDomain]);

        return $this->render(
            'page/privacy_policy.html.twig',
            [
                'body' => $site ? $site->privacyPolicy : ''
            ]
        );
    }
}
