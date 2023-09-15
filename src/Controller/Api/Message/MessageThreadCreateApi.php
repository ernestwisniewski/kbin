<?php

declare(strict_types=1);

namespace App\Controller\Api\Message;

use App\Controller\Traits\PrivateContentTrait;
use App\DTO\MessageDto;
use App\DTO\MessageThreadResponseDto;
use App\Entity\User;
use App\Service\MessageManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageThreadCreateApi extends MessageBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 201,
        description: 'Message thread created',
        content: new Model(type: MessageThreadResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'The request body was invalid',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not permitted to message this user',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
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
        name: 'user_id',
        in: 'path',
        description: 'User being messaged',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'd',
        in: 'query',
        description: 'Number of replies returned',
        schema: new OA\Schema(type: 'integer', default: self::REPLY_DEPTH, minimum: self::MIN_REPLY_DEPTH, maximum: self::MAX_REPLY_DEPTH)
    )]
    #[OA\RequestBody(content: new Model(type: MessageDto::class))]
    #[OA\Tag(name: 'message')]
    #[Security(name: 'oauth2', scopes: ['user:message:create'])]
    #[IsGranted('ROLE_OAUTH2_USER:MESSAGE:CREATE')]
    #[IsGranted('message', subject: 'receiver', statusCode: 403)]
    public function __invoke(
        #[MapEntity(id: 'user_id')]
        User $receiver,
        MessageManager $manager,
        ValidatorInterface $validator,
        RateLimiterFactory $apiMessageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiMessageLimiter);

        if ($receiver->apId) {
            throw new AccessDeniedHttpException();
        }

        $dto = $this->deserializeMessage();

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $thread = $manager->toThread($dto, $this->getUserOrThrow(), $receiver);

        return new JsonResponse(
            $this->serializeMessageThread($thread),
            status: 201,
            headers: $headers
        );
    }
}
