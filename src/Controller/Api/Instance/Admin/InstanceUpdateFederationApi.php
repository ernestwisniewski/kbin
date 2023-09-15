<?php

declare(strict_types=1);

namespace App\Controller\Api\Instance\Admin;

use App\Controller\Api\Instance\InstanceBaseApi;
use App\DTO\InstancesDto;
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

class InstanceUpdateFederationApi extends InstanceBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Defederated instances updated',
        content: new Model(type: InstancesDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'One of the URLs entered was invalid',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You do not have permission to edit the list of defederated instances',
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
    #[OA\RequestBody(content: new Model(type: InstancesDto::class))]
    #[OA\Tag(name: 'admin/federation')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:federation:update'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:FEDERATION:UPDATE')]
    public function __invoke(
        SettingsManager $settings,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        /** @var InstancesDto $dto */
        $dto = $serializer->deserialize($request->getContent(), InstancesDto::class, 'json');

        $dto->instances = array_map(
            fn (string $instance) => trim(str_replace('www.', '', $instance)),
            $dto->instances
        );

        $errors = $validator->validate($dto);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $settings->set('KBIN_BANNED_INSTANCES', $dto->instances);

        $dto = new InstancesDto($settings->get('KBIN_BANNED_INSTANCES'));

        return new JsonResponse(
            $dto,
            headers: $headers
        );
    }
}
