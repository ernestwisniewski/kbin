<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\SiteRepository;
use App\Service\SettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TermsController extends AbstractController
{
    public function __construct(private readonly PageRepository $repository)
    {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'page/terms.html.twig',
            [
                'body' => $this->repository->findOneBy(['name' => 'terms'])?->body,
            ]
        );
    }
}
