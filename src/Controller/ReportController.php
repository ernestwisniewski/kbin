<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ReportDto;
use App\Entity\Contracts\ReportInterface;
use App\Exception\SubjectHasBeenReportedException;
use App\Form\ReportType;
use App\Service\ReportManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
    public function __construct(
        private readonly ReportManager $manager,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(ReportInterface $subject, Request $request): Response
    {
        $dto = ReportDto::create($subject);

        $form = $this->getForm($dto, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleReportRequest($dto, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'report/_form_report.html.twig');
        }

        return $this->render(
            'report/create.html.twig',
            [
                'form' => $form->createView(),
                'magazine' => $subject->magazine,
                'subject' => $subject,
            ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    private function getForm(ReportDto $dto, ReportInterface $subject): FormInterface
    {
        return $this->createForm(
            ReportType::class,
            $dto,
            [
                'action' => $this->generateUrl($dto->getRouteName(), ['id' => $subject->getId()]),
            ]
        );
    }

    private function handleReportRequest(ReportDto $dto, Request $request): Response
    {
        try {
            $this->manager->report($dto, $this->getUserOrThrow());
            $reportError = false;
            $responseMessage = $this->translator->trans('subject_reported');
        } catch (SubjectHasBeenReportedException $exception) {
            $reportError = true;
            $responseMessage = $this->translator->trans('subject_reported_exists');
        } finally {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(
                    [
                        'success' => true,
                        'html' => sprintf("<div class='alert %s'>%s</div>", ($reportError) ? 'alert__danger' : 'alert__info', $responseMessage),
                    ]
                );
            }

            $this->addFlash($reportError ? 'error' : 'info', $responseMessage);

            return $this->redirectToRefererOrHome($request);
        }
    }
}
