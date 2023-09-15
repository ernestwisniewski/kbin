<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry;

use App\Controller\Traits\PrivateContentTrait;
use App\DTO\EntryDto;
use App\DTO\EntryRequestDto;
use App\DTO\EntryResponseDto;
use App\DTO\ImageUploadDto;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Service\EntryManager;
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

class MagazineEntryCreateApi extends EntriesBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 201,
        description: 'Returns the created Entry',
        content: new Model(type: EntryResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'An entry must have at least one of URL, body, or image',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'Either the entry:create scope has not been granted, or the user is banned from the magazine',
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine to create the entry in',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(content: new Model(
        type: EntryRequestDto::class,
        groups: [
            Entry::ENTRY_TYPE_ARTICLE,
            'common',
        ]
    ))]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['entry:create'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY:CREATE')]
    public function article(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        EntryManager $manager,
        RateLimiterFactory $apiEntryLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiEntryLimiter);

        $entry = $this->createEntry($magazine, $manager, context: [
            'groups' => [
                Entry::ENTRY_TYPE_ARTICLE,
                'common',
            ],
        ]);

        return new JsonResponse(
            $this->serializeEntry($manager->createDto($entry)),
            status: 201,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 201,
        description: 'Returns the created Entry',
        content: new Model(type: EntryResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'An entry must have at least one of URL, body, or image',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'Either the entry:create scope has not been granted, or the user is banned from the magazine',
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine to create the entry in',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(content: new Model(
        type: EntryRequestDto::class,
        groups: [
            Entry::ENTRY_TYPE_LINK,
            'common',
        ]
    ))]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['entry:create'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY:CREATE')]
    public function link(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        EntryManager $manager,
        RateLimiterFactory $apiEntryLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiEntryLimiter);

        $entry = $this->createEntry($magazine, $manager, context: [
            'groups' => [
                Entry::ENTRY_TYPE_LINK,
                'common',
            ],
        ]);

        return new JsonResponse(
            $this->serializeEntry($manager->createDto($entry)),
            status: 201,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 201,
        description: 'Returns the created Entry',
        content: new Model(type: EntryResponseDto::class),
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
        description: 'Either the entry:create scope has not been granted, or the user is banned from the magazine',
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine to create the entry in',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(content: new Model(
        type: EntryRequestDto::class,
        groups: [
            Entry::ENTRY_TYPE_VIDEO,
            'common',
        ]
    ))]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['entry:create'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY:CREATE')]
    public function video(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        EntryManager $manager,
        RateLimiterFactory $apiEntryLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiEntryLimiter);

        $entry = $this->createEntry($magazine, $manager, [
            'groups' => [
                Entry::ENTRY_TYPE_VIDEO,
                'common',
            ],
        ]);

        return new JsonResponse(
            $this->serializeEntry($manager->createDto($entry)),
            status: 201,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 201,
        description: 'Returns the created image entry',
        content: new Model(type: EntryResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'Image was too large, not provided, or is not an acceptable file type',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'Either the entry:create scope has not been granted, or the user is banned from the magazine',
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine to create the entry in',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(content: new OA\MediaType(
        'multipart/form-data',
        schema: new OA\Schema(
            ref: new Model(
                type: EntryRequestDto::class,
                groups: [
                    ImageUploadDto::IMAGE_UPLOAD,
                    Entry::ENTRY_TYPE_IMAGE,
                    'common',
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
    #[Security(name: 'oauth2', scopes: ['entry:create'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY:CREATE')]
    public function uploadImage(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        ValidatorInterface $validator,
        EntryManager $manager,
        RateLimiterFactory $apiImageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiImageLimiter);

        $dto = new EntryDto();
        $dto->magazine = $magazine;

        if (null === $dto->magazine) {
            throw new NotFoundHttpException('Magazine not found');
        }

        $deserialized = $this->deserializeEntryFromForm();

        $dto = $deserialized->mergeIntoDto($dto);

        if (!$this->isGranted('create_content', $dto->magazine)) {
            throw new AccessDeniedHttpException();
        }

        $image = $this->handleUploadedImage();

        if (null !== $image) {
            $dto->image = $this->imageFactory->createDto($image);
        }

        $errors = $validator->validate($dto);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $entry = $manager->create($dto, $this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeEntry($manager->createDto($entry)),
            status: 201,
            headers: $headers
        );
    }
}
