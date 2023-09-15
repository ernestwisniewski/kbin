<?php

declare(strict_types=1);

namespace App\Controller\Api\Instance\Admin;

use App\Controller\Api\Instance\InstanceBaseApi;
use App\DTO\SettingsDto;
use App\Service\SettingsManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InstanceUpdateSettingsApi extends InstanceBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Settings updated',
        content: new Model(type: SettingsDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid settings provided',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You do not have permission to edit the instance settings',
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
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: SettingsDto::class)))]
    #[OA\Tag(name: 'admin/instance')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:instance:settings:edit'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:INSTANCE:SETTINGS:EDIT')]
    public function __invoke(
        SettingsManager $settings,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        /** @var SettingsDto $dto */
        $dto = $serializer->deserialize($request->getContent(), SettingsDto::class, 'json');

        $errors = $validator->validate($dto);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $settingDto = $dto->mergeIntoDto($settings->getDto());

        $settings->save($settingDto);

        return new JsonResponse(
            $settingDto,
            headers: $headers
        );
    }
}
