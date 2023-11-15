<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Response;

class PrivacyPolicyController extends AbstractController
{
    public function __construct(private readonly PageRepository $repository)
    {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'page/privacy_policy.html.twig',
            [
                'body' => $this->repository->findOneBy(['name' => 'terms'])?->body,
            ]
        );
    }
}
