<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\InstanceStatsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AboutController extends AbstractController
{
    public function __construct(private InstanceStatsManager $counter)
    {
    }

    public
    function __invoke(
        Request $request
    ): Response {
        return $this->render(
            'landing/about.html.twig',
            $this->counter->count()
        );
    }
}
