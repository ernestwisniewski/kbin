<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Moderate;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\DTO\ReportResponseDto;
use App\Entity\Magazine;
use App\Entity\Report;
use App\Repository\MagazineRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineReportsRetrieveApi extends MagazineBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns a report',
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
        description: 'You are not allowed to retrieve reports for this magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
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
        description: 'The magazine of the report',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'report_id',
        in: 'path',
        description: 'The report to retrieve',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'moderation/magazine')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine:reports:read'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE:REPORTS:READ')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function __invoke(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        #[MapEntity(id: 'report_id')]
        Report $report,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        if ($magazine->getId() !== $report->magazine->getId()) {
            throw new NotFoundHttpException('The report was not found in the magazine');
        }

        return new JsonResponse(
            $this->serializeReport($report),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of reports',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ReportResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
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
        description: 'Magazine to retrieve reports from',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of reports to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of reports per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: MagazineRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Filter by report status',
        in: 'query',
        schema: new OA\Schema(type: 'string', default: Report::STATUS_PENDING, enum: Report::STATUS_OPTIONS)
    )]
    #[OA\Tag(name: 'moderation/magazine')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine:reports:read'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE:REPORTS:READ')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function collection(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        MagazineRepository $repository,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        $status = $request->get('status', Report::STATUS_PENDING);
        if (false === array_search($status, Report::STATUS_OPTIONS)) {
            throw new BadRequestHttpException('Invalid status');
        }

        $reports = $repository->findReports(
            $magazine,
            $this->getPageNb($request),
            self::constrainPerPage($request->get('perPage', MagazineRepository::PER_PAGE)),
            $status
        );

        $dtos = [];
        foreach ($reports->getCurrentPageResults() as $value) {
            \assert($value instanceof Report);
            array_push($dtos, $this->serializeReport($value));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $reports),
            headers: $headers
        );
    }
}
