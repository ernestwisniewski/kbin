<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Form\SettingsType;
use App\Service\SettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends AbstractController
{
    public function __construct(private SettingsManager $settings)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request): Response
    {
        $dto = $this->settings->getDto();

        $form = $this->createForm(SettingsType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->settings->save($dto);

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('admin/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
