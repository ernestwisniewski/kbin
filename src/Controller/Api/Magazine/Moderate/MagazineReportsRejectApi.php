<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Moderate;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\DTO\ReportResponseDto;
use App\Entity\Magazine;
use App\Entity\Report;
use App\Service\ReportManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineReportsRejectApi extends MagazineBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Reject a report',
        content: new Model(type: ReportResponseDto::class),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not allowed to accept this report',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Report not found',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\NotFoundErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\Parameter(
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine the report is in',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'report_id',
        in: 'path',
        description: 'The report to reject',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'moderation/magazine')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine:reports:action'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE:REPORTS:ACTION')]
    #[IsGranted('moderate', subject: 'magazine')]
    /**
     * Rejecting a report will preserve the reported item.
     */
    public function __invoke(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        #[MapEntity(id: 'report_id')]
        Report $report,
        ReportManager $manager,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        if ($magazine->getId() !== $report->magazine->getId()) {
            throw new NotFoundHttpException('Report not found in magazine');
        }

        $manager->reject($report, $this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeReport($report),
            headers: $headers
        );
    }
}
