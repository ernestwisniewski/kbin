<?php

declare(strict_types=1);

namespace App\Controller\Api\Message;

use App\DTO\MessageResponseDto;
use App\Entity\Message;
use App\Service\MessageManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageReadApi extends MessageBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Marks the message as read',
        content: new Model(type: MessageResponseDto::class),
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
        response: 404,
        description: 'Message not found',
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
        name: 'message_id',
        in: 'path',
        description: 'The message to read',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'message')]
    #[Security(name: 'oauth2', scopes: ['user:message:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:MESSAGE:READ')]
    public function read(
        #[MapEntity(id: 'message_id')]
        Message $message,
        MessageManager $manager,
        RateLimiterFactory $apiUpdateLimiter,
    ): JsonResponse {
        if (!$this->isGranted('show', $message->thread)) {
            throw new AccessDeniedHttpException();
        }

        $headers = $this->rateLimit($apiUpdateLimiter);

        $manager->readMessage($message, $this->getUserOrThrow(), flush: true);

        return new JsonResponse(
            $this->serializeMessage($message),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Marks the message as new',
        content: new Model(type: MessageResponseDto::class),
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
        response: 404,
        description: 'Message not found',
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
        name: 'message_id',
        in: 'path',
        description: 'The message to mark as new',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'message')]
    #[Security(name: 'oauth2', scopes: ['user:message:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:MESSAGE:READ')]
    public function unread(
        #[MapEntity(id: 'message_id')]
        Message $message,
        MessageManager $manager,
        RateLimiterFactory $apiUpdateLimiter,
    ): JsonResponse {
        if (!$this->isGranted('show', $message->thread)) {
            throw new AccessDeniedHttpException();
        }

        $headers = $this->rateLimit($apiUpdateLimiter);

        $manager->unreadMessage($message, $this->getUserOrThrow(), flush: true);

        return new JsonResponse(
            $this->serializeMessage($message),
            headers: $headers
        );
    }
}
