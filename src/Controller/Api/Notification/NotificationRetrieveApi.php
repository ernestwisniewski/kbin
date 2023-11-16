<?php

declare(strict_types=1);

namespace App\Controller\Api\Notification;

use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Schema\NotificationSchema;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotificationRetrieveApi extends NotificationBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns the Notification',
        content: new Model(type: NotificationSchema::class),
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
        description: 'You do not have permission to view this notification',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Notification not found',
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
        name: 'notification_id',
        in: 'path',
        description: 'The notification to retrieve',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'notification')]
    #[Security(name: 'oauth2', scopes: ['user:notification:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:NOTIFICATION:READ')]
    #[IsGranted('view', 'notification')]
    public function __invoke(
        #[MapEntity(id: 'notification_id')]
        Notification $notification,
        RateLimiterFactory $apiNotificationLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiNotificationLimiter);

        return new JsonResponse(
            $this->serializeNotification($notification),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of notifications for the current user',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: NotificationSchema::class))
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
        response: 400,
        description: 'Invalid status type requested',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You do not have permission to view notifications',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
        name: 'p',
        description: 'Page of notifications to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of notifications per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: NotificationRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Notification status to retrieve',
        in: 'path',
        schema: new OA\Schema(
            type: 'string',
            default: NotificationRepository::STATUS_ALL,
            enum: NotificationRepository::STATUS_OPTIONS
        )
    )]
    #[OA\Tag(name: 'notification')]
    #[Security(name: 'oauth2', scopes: ['user:notification:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:NOTIFICATION:READ')]
    public function collection(
        string $status,
        NotificationRepository $repository,
        RateLimiterFactory $apiNotificationLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiNotificationLimiter);

        // 0 is falsy so need to compare with false to be certain the item was not found
        if (false === array_search($status, NotificationRepository::STATUS_OPTIONS)) {
            throw new BadRequestHttpException();
        }

        $request = $this->request->getCurrentRequest();
        $notifications = $repository->findByUser(
            $this->getUserOrThrow(),
            $this->getPageNb($request),
            $status,
            $this->constrainPerPage($request->get('perPage', NotificationRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($notifications->getCurrentPageResults() as $value) {
            array_push($dtos, $this->serializeNotification($value));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $notifications),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns the number of unread notifications for the current user',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'count',
                    type: 'integer',
                    minimum: 0
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
        response: 403,
        description: 'You do not have permission to view notification counts',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
    #[OA\Tag(name: 'notification')]
    #[Security(name: 'oauth2', scopes: ['user:notification:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:NOTIFICATION:READ')]
    public function count(
        NotificationRepository $repository,
        RateLimiterFactory $apiNotificationLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiNotificationLimiter);

        $count = $repository->countUnreadNotifications($this->getUserOrThrow());

        return new JsonResponse(
            [
                'count' => $count,
            ],
            headers: $headers
        );
    }
}
