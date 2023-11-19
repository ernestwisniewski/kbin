<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Message;

use App\Controller\Traits\PrivateContentTrait;
use App\DTO\MessageResponseDto;
use App\DTO\MessageThreadResponseDto;
use App\Entity\MessageThread;
use App\Kbin\Message\MessageThreadPageView;
use App\Kbin\User\DTO\UserResponseDto;
use App\Kbin\User\Factory\UserFactory;
use App\Repository\Criteria;
use App\Repository\MessageRepository;
use App\Repository\MessageThreadRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageRetrieveApi extends MessageBaseApi
{
    use PrivateContentTrait;

    public const MESSAGES_PER_PAGE = 25;

    #[OA\Response(
        response: 200,
        description: 'Returns the Message',
        content: new Model(type: MessageResponseDto::class),
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
        response: 404,
        description: 'Message not found',
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
        name: 'message_id',
        in: 'path',
        description: 'The message to retrieve',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'message')]
    #[Security(name: 'oauth2', scopes: ['user:message:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:MESSAGE:READ')]
    public function __invoke(
        MessageRepository $repository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $message = $repository->find((int) $this->request->getCurrentRequest()->get('message_id'));
        if (null === $message) {
            throw new NotFoundHttpException();
        }
        if (!$this->isGranted('show', $message->thread)) {
            throw new AccessDeniedHttpException();
        }

        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        return new JsonResponse(
            $this->serializeMessage($message),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of message threads for the current user',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MessageThreadResponseDto::class))
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
        name: 'p',
        description: 'Page of messages to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of messages per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: MessageThreadRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'd',
        description: 'Number of replies per thread',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: self::REPLY_DEPTH,
            minimum: self::MIN_REPLY_DEPTH,
            maximum: self::MAX_REPLY_DEPTH
        )
    )]
    #[OA\Tag(name: 'message')]
    #[Security(name: 'oauth2', scopes: ['user:message:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:MESSAGE:READ')]
    public function collection(
        MessageThreadRepository $repository,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $messages = $repository->findUserMessages(
            $this->getUserOrThrow(),
            $this->getPageNb($request),
            $this->constrainPerPage($request->get('perPage', MessageThreadRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($messages->getCurrentPageResults() as $value) {
            array_push($dtos, $this->serializeMessageThread($value));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $messages),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of messages in a thread',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MessageResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
                new OA\Property(
                    property: 'participants',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: UserResponseDto::class))
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
        description: 'You are not allowed to view the messages in this thread',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Page not found',
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
        name: 'thread_id',
        description: 'Thread from which to retrieve messages',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of messages to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of messages per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: MessageRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'sort',
        description: 'Order to retrieve messages by',
        in: 'path',
        schema: new OA\Schema(type: 'string', default: Criteria::SORT_NEW, enum: MessageThreadPageView::SORT_OPTIONS)
    )]
    #[OA\Tag(name: 'message')]
    #[Security(name: 'oauth2', scopes: ['user:message:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:MESSAGE:READ')]
    #[IsGranted('show', subject: 'thread', statusCode: 403)]
    public function thread(
        #[MapEntity(id: 'thread_id')]
        MessageThread $thread,
        MessageRepository $repository,
        UserFactory $userFactory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $criteria = new MessageThreadPageView($this->getPageNb($request));
        $criteria->perPage = $this->constrainPerPage($request->get('perPage', self::MESSAGES_PER_PAGE));
        $criteria->thread = $thread;
        $criteria->sortOption = $request->get('sort', Criteria::SORT_NEW);

        $messages = $repository->findByCriteria($criteria);

        $dtos = [];
        foreach ($messages->getCurrentPageResults() as $value) {
            array_push($dtos, $this->serializeMessage($value));
        }

        $paginated = $this->serializePaginated($dtos, $messages);
        $paginated['participants'] = array_map(
            fn ($participant) => new UserResponseDto($userFactory->createDto($participant)),
            $thread->participants->toArray()
        );

        return new JsonResponse(
            $paginated,
            headers: $headers
        );
    }
}
