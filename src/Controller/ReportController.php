<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Contracts\ReportInterface;
use App\Service\ReportManager;
use App\Form\ReportType;
use App\DTO\ReportDto;

class ReportController extends AbstractController
{
    public function __construct(private ReportManager $reportManager)
    {
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
            $this->reportManager->report($reportDto, $this->getUserOrThrow());

            if ($request->isXmlHttpRequest()) {
                return $this->getJsonSuccessResponse();
            }

            // @todo flash message
            return $this->redirectToRefererOrHome($request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'report/_form.html.twig');
        }

        return $this->render(
            'report/single.html.twig',
            [
                'form'     => $form->createView(),
                'magazine' => $subject->magazine,
                'subject'  => $subject,
            ]
        );
    }
}
