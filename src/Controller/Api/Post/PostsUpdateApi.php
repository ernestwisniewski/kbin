<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Post;

use App\Entity\Post;
use App\Kbin\Post\DTO\PostRequestDto;
use App\Kbin\Post\DTO\PostResponseDto;
use App\Kbin\Post\Factory\PostFactory;
use App\Kbin\Post\PostEdit;
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

class PostsUpdateApi extends PostsBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Post updated',
        content: new Model(type: PostResponseDto::class),
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
        description: 'You do not have permission to update this post',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Post not found',
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
        name: 'post_id',
        in: 'path',
        description: 'The id of the post to update',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(content: new Model(
        type: PostRequestDto::class,
        groups: [
            'common',
            'post',
            'no-upload',
        ]
    ))]
    #[OA\Tag(name: 'post')]
    #[Security(name: 'oauth2', scopes: ['post:edit'])]
    #[IsGranted('ROLE_OAUTH2_POST:EDIT')]
    #[IsGranted('edit', subject: 'post')]
    public function __invoke(
        #[MapEntity(id: 'post_id')]
        Post $post,
        PostEdit $postEdit,
        PostFactory $postFactory,
        ValidatorInterface $validator,
        PostFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        if ($post->user->getId() !== $this->getUserOrThrow()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $dto = $this->deserializePost($postFactory->createDto($post));

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $post = $postEdit($post, $dto);

        return new JsonResponse(
            $this->serializePost($factory->createDto($post)),
            headers: $headers
        );
    }
}
