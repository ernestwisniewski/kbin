<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Post\Comments;

use App\Controller\Api\Post\PostsBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\DTO\ImageUploadDto;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\PostComment\DTO\PostCommentRequestDto;
use App\Kbin\PostComment\DTO\PostCommentResponseDto;
use App\Kbin\PostComment\Factory\PostCommentFactory;
use App\Kbin\PostComment\PostCommentCreate;
use App\Service\ImageManager;
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

class PostCommentsCreateApi extends PostsBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 201,
        description: 'Post comment created',
        content: new Model(type: PostCommentResponseDto::class),
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
        description: 'The request body was invalid or the comment you are replying to does not belong to the post you are trying to add the new comment to.',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not permitted to add comments to this post',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Post or parent comment not found',
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
        description: 'Post to which the new comment will belong',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(content: new Model(
        type: PostCommentRequestDto::class,
        groups: [
            'common',
            'comment',
            'no-upload',
        ]
    ))]
    #[OA\Tag(name: 'post_comment')]
    #[Security(name: 'oauth2', scopes: ['post_comment:create'])]
    #[IsGranted('ROLE_OAUTH2_POST_COMMENT:CREATE')]
    #[IsGranted('comment', subject: 'post')]
    public function __invoke(
        #[MapEntity(id: 'post_id')]
        Post $post,
        #[MapEntity(id: 'comment_id')]
        ?PostComment $parent,
        PostCommentCreate $postCommentCreate,
        PostCommentFactory $factory,
        ValidatorInterface $validator,
        RateLimiterFactory $apiCommentLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiCommentLimiter);

        if (!$this->isGranted('create_content', $post->magazine)) {
            throw new AccessDeniedHttpException();
        }

        if ($parent && $parent->post->getId() !== $post->getId()) {
            throw new BadRequestHttpException('The parent comment does not belong to that post!');
        }
        $dto = $this->deserializePostComment();

        $dto->post = $post;
        $dto->magazine = $post->magazine;
        $dto->parent = $parent;

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        // Rate limit handled above
        $comment = $postCommentCreate($dto, $this->getUserOrThrow(), rateLimit: false);

        return new JsonResponse(
            $this->serializePostComment($factory->createDto($comment)),
            status: 201,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 201,
        description: 'Post comment created',
        content: new Model(type: PostCommentResponseDto::class),
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
        description: 'The request body was invalid or the comment you are replying to does not belong to the post you are trying to add the new comment to.',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not permitted to add comments to this post',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Post or parent comment not found',
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
        description: 'Post to which the new comment will belong',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(content: new OA\MediaType(
        'multipart/form-data',
        schema: new OA\Schema(
            ref: new Model(
                type: PostCommentRequestDto::class,
                groups: [
                    'common',
                    'comment',
                    ImageUploadDto::IMAGE_UPLOAD,
                ]
            )
        ),
        encoding: [
            'imageUpload' => [
                'contentType' => ImageManager::IMAGE_MIMETYPE_STR,
            ],
        ]
    ))]
    #[OA\Tag(name: 'post_comment')]
    #[Security(name: 'oauth2', scopes: ['post_comment:create'])]
    #[IsGranted('ROLE_OAUTH2_POST_COMMENT:CREATE')]
    #[IsGranted('comment', subject: 'post')]
    public function uploadImage(
        #[MapEntity(id: 'post_id')]
        Post $post,
        #[MapEntity(id: 'comment_id')]
        ?PostComment $parent,
        PostCommentCreate $postCommentCreate,
        PostCommentFactory $factory,
        ValidatorInterface $validator,
        RateLimiterFactory $apiImageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiImageLimiter);

        if (!$this->isGranted('create_content', $post->magazine)) {
            throw new AccessDeniedHttpException();
        }

        $image = $this->handleUploadedImage();

        if ($parent && $parent->post->getId() !== $post->getId()) {
            throw new BadRequestHttpException('The parent comment does not belong to that post!');
        }
        $dto = $this->deserializePostCommentFromForm();

        $dto->post = $post;
        $dto->magazine = $post->magazine;
        $dto->parent = $parent;
        $dto->image = $this->imageFactory->createDto($image);

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        // Rate limit handled above
        $comment = $postCommentCreate($dto, $this->getUserOrThrow(), rateLimit: false);

        return new JsonResponse(
            $this->serializePostComment($factory->createDto($comment)),
            status: 201,
            headers: $headers
        );
    }
}
