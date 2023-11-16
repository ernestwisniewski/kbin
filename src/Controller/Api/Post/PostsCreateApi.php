<?php

declare(strict_types=1);

namespace App\Controller\Api\Post;

use App\Controller\Traits\PrivateContentTrait;
use App\DTO\ImageUploadDto;
use App\DTO\PostDto;
use App\DTO\PostRequestDto;
use App\DTO\PostResponseDto;
use App\Entity\Magazine;
use App\Factory\PostFactory;
use App\Kbin\Post\PostCreate;
use App\Service\ImageManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostsCreateApi extends PostsBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 201,
        description: 'Post created',
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
        description: 'Banned from magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Magazine not found',
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
    #[OA\RequestBody(content: new Model(
        type: PostRequestDto::class,
        groups: [
            'common',
            'post',
            'no-upload',
        ]
    ))]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['post:create'])]
    #[IsGranted('ROLE_OAUTH2_POST:CREATE')]
    public function __invoke(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        PostCreate $postCreate,
        PostFactory $postFactory,
        ValidatorInterface $validator,
        RateLimiterFactory $apiPostLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiPostLimiter);

        if (!$this->isGranted('create_content', $magazine)) {
            throw new AccessDeniedHttpException('Create content permission not granted');
        }

        $dto = new PostDto();
        $dto->magazine = $magazine;

        if (null === $dto->magazine) {
            throw new NotFoundHttpException('Magazine not found');
        }

        $dto = $this->deserializePost($dto);

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        // Rate limit handled elsewhere
        $post = $postCreate($dto, $this->getUserOrThrow(), rateLimit: false);

        return new JsonResponse(
            $this->serializePost($postFactory->createDto($post)),
            status: 201,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 201,
        description: 'Post created',
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
        description: 'Banned from magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Magazine not found',
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
    #[OA\RequestBody(content: new OA\MediaType(
        'multipart/form-data',
        schema: new OA\Schema(
            ref: new Model(
                type: PostRequestDto::class,
                groups: [
                    'common',
                    'post',
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
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['post:create'])]
    #[IsGranted('ROLE_OAUTH2_POST:CREATE')]
    public function uploadImage(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        PostCreate $postCreate,
        PostFactory $postFactory,
        ValidatorInterface $validator,
        RateLimiterFactory $apiImageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiImageLimiter);

        if (!$this->isGranted('create_content', $magazine)) {
            throw new AccessDeniedHttpException('Create content permission not granted');
        }

        $image = $this->handleUploadedImage();

        $dto = new PostDto();
        $dto->magazine = $magazine;
        $dto->image = $this->imageFactory->createDto($image);

        if (null === $dto->magazine) {
            throw new NotFoundHttpException('Magazine not found');
        }

        $dto = $this->deserializePostFromForm($dto);

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        // Rate limit handled elsewhere
        $post = $postCreate($dto, $this->getUserOrThrow(), rateLimit: false);

        return new JsonResponse(
            $this->serializePost($postFactory->createDto($post)),
            status: 201,
            headers: $headers
        );
    }
}
