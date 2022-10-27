<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;


class ClearCacheController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request, KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
        ]);

        $application->run($input);

        return $this->redirectToRefererOrHome($request);
    }
}
