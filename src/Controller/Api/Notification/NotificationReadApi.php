<?php

declare(strict_types=1);

namespace App\Controller\Api\Notification;

use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Notification;
use App\Schema\NotificationSchema;
use App\Service\NotificationManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotificationReadApi extends NotificationBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Marked the notification as read',
        content: new Model(type: NotificationSchema::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not allowed to mark this notification as read',
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'notification_id',
        in: 'path',
        description: 'The notification to read',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'notification')]
    #[Security(name: 'oauth2', scopes: ['user:notification:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:NOTIFICATION:READ')]
    #[IsGranted('view', 'notification')]
    public function read(
        #[MapEntity(id: 'notification_id')]
        Notification $notification,
        RateLimiterFactory $apiNotificationLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiNotificationLimiter);

        $notification->status = Notification::STATUS_READ;
        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializeNotification($notification),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 204,
        description: 'Marked all notifications as read',
        content: null,
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not allowed to mark notifications as read',
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Tag(name: 'notification')]
    #[Security(name: 'oauth2', scopes: ['user:notification:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:NOTIFICATION:READ')]
    public function readAll(
        NotificationManager $manager,
        RateLimiterFactory $apiNotificationLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiNotificationLimiter);

        $manager->markAllAsRead($this->getUserOrThrow());

        return new JsonResponse(
            status: 204,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Marked the notification as new',
        content: new Model(type: NotificationSchema::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not allowed to mark this notification as new',
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'notification_id',
        in: 'path',
        description: 'The notification to mark as new',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'notification')]
    #[Security(name: 'oauth2', scopes: ['user:notification:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:NOTIFICATION:READ')]
    #[IsGranted('view', 'notification')]
    public function unread(
        #[MapEntity(id: 'notification_id')]
        Notification $notification,
        RateLimiterFactory $apiNotificationLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiNotificationLimiter);

        $notification->status = Notification::STATUS_NEW;
        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializeNotification($notification),
            headers: $headers
        );
    }
}
