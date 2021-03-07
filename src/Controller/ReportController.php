<?php

namespace App\Controller;

use App\DTO\ReportDto;
use App\Entity\Contracts\ReportInterface;
use App\Form\ReportType;
use App\Service\ReportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    private ReportManager $reportManager;

    public function __construct(ReportManager $reportManager)
    {
        $this->reportManager = $reportManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(ReportInterface $subject, Request $request): Response
    {
        $reportDto = (new ReportDto())->create($subject);

        $form = $this->createForm(
            ReportType::class,
            $reportDto,
            [
                'action' => $this->generateUrl($reportDto->getRouteName(), ['id' => $subject->getId()]),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reportManager->report($reportDto);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(
                    [
                        'success' => true,
                    ]
                );
            }

            return $this->redirectToRoute('front_magazine', ['name' => $subject->getMagazine()->getName()]);
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'form' => $this->renderView(
                        'report/_form.html.twig',
                        [
                            'form' => $form->createView(),
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'report/single.html.twig',
            [
                'form'     => $form->createView(),
                'magazine' => $subject->getMagazine(),
                'subject'  => $subject,
            ]
        );
    }

}
