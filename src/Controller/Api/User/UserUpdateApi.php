<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\DTO\UserProfileRequestDto;
use App\DTO\UserResponseDto;
use App\DTO\UserSettingsDto;
use App\Factory\UserFactory;
use App\Service\UserManager;
use App\Service\UserSettingsManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserUpdateApi extends UserBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'User updated',
        content: new Model(type: UserResponseDto::class),
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
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\RequestBody(content: new Model(type: UserProfileRequestDto::class))]
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:profile:edit'])]
    #[IsGranted('ROLE_OAUTH2_USER:PROFILE:EDIT')]
    public function profile(
        UserManager $manager,
        ValidatorInterface $validator,
        UserFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        $request = $this->request->getCurrentRequest();
        /** @var UserProfileRequestDto $dto */
        $deserialized = $this->serializer->deserialize($request->getContent(), UserProfileRequestDto::class, 'json');

        $errors = $validator->validate($deserialized);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $dto = $manager->createDto($this->getUserOrThrow());

        $dto->about = $deserialized->about;

        $user = $manager->edit($this->getUserOrThrow(), $dto);

        return new JsonResponse(
            $this->serializeUser($factory->createDto($user)),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'User settings updated',
        content: new Model(type: UserSettingsDto::class),
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
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\RequestBody(content: new Model(type: UserSettingsDto::class))]
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:profile:edit'])]
    #[IsGranted('ROLE_OAUTH2_USER:PROFILE:EDIT')]
    public function settings(
        UserSettingsManager $manager,
        ValidatorInterface $validator,
        RateLimiterFactory $apiUpdateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        $settings = $manager->createDto($this->getUserOrThrow());

        $dto = $this->deserializeUserSettings($settings);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $manager->update($this->getUserOrThrow(), $dto);

        return new JsonResponse(
            $manager->createDto($this->getUserOrThrow()),
            headers: $headers
        );
    }
}
