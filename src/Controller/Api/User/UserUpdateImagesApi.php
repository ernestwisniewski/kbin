<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\DTO\ImageUploadDto;
use App\DTO\UserResponseDto;
use App\Factory\UserFactory;
use App\Service\ImageManager;
use App\Service\UserManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserUpdateImagesApi extends UserBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'User avatar updated',
        content: new Model(type: UserResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'The uploaded image was missing or invalid',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not authorized to update the user\'s profile',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
    #[OA\RequestBody(content: new OA\MediaType(
        'multipart/form-data',
        schema: new OA\Schema(
            ref: new Model(
                type: ImageUploadDto::class,
                groups: [
                    ImageUploadDto::IMAGE_UPLOAD_NO_ALT,
                ]
            )
        ),
        encoding: [
            'imageUpload' => [
                'contentType' => ImageManager::IMAGE_MIMETYPE_STR,
            ],
        ]
    ))]
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:profile:edit'])]
    #[IsGranted('ROLE_OAUTH2_USER:PROFILE:EDIT')]
    public function avatar(
        UserManager $manager,
        UserFactory $factory,
        RateLimiterFactory $apiImageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiImageLimiter);

        $image = $this->handleUploadedImage();

        $dto = $manager->createDto($this->getUserOrThrow());

        $dto->avatar = $image ? $this->imageFactory->createDto($image) : $dto->avatar;

        $user = $manager->edit($this->getUserOrThrow(), $dto);

        return new JsonResponse(
            $this->serializeUser($factory->createDto($user)),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'User cover updated',
        content: new Model(type: UserResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'The uploaded image was missing or invalid',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not authorized to update the user\'s profile',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
    #[OA\RequestBody(content: new OA\MediaType(
        'multipart/form-data',
        schema: new OA\Schema(
            ref: new Model(
                type: ImageUploadDto::class,
                groups: [
                    ImageUploadDto::IMAGE_UPLOAD_NO_ALT,
                ]
            )
        ),
        encoding: [
            'imageUpload' => [
                'contentType' => ImageManager::IMAGE_MIMETYPE_STR,
            ],
        ]
    ))]
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:profile:edit'])]
    #[IsGranted('ROLE_OAUTH2_USER:PROFILE:EDIT')]
    public function cover(
        UserManager $manager,
        UserFactory $factory,
        RateLimiterFactory $apiImageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiImageLimiter);

        $image = $this->handleUploadedImage();

        $dto = $manager->createDto($this->getUserOrThrow());

        $dto->cover = $image ? $this->imageFactory->createDto($image) : $dto->cover;

        $user = $manager->edit($this->getUserOrThrow(), $dto);

        return new JsonResponse(
            $this->serializeUser($factory->createDto($user)),
            headers: $headers
        );
    }
}
