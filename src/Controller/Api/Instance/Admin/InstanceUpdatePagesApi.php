<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Instance\Admin;

use App\Controller\Api\Instance\InstanceBaseApi;
use App\DTO\SiteResponseDto;
use App\Entity\Site;
use App\Kbin\StaticPage\DTO\StaticPageDto;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InstanceUpdatePagesApi extends InstanceBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Page updated',
        content: new Model(type: SiteResponseDto::class),
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
        description: 'You do not have permission to edit the instance pages',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: StaticPageDto::class)))]
    #[OA\Parameter(
        name: 'page',
        in: 'path',
        schema: new OA\Schema(type: 'string', enum: SiteResponseDto::PAGES)
    )]
    #[OA\Tag(name: 'admin/instance')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:instance:information:edit'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:INSTANCE:INFORMATION:EDIT')]
    public function __invoke(
        SiteRepository $repository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        $page = $request->get('page');

        if (null === $page || false === array_search($page, SiteResponseDto::PAGES)) {
            throw new BadRequestHttpException('Page parameter is invalid!');
        }

        /** @var StaticPageDto $dto */
        $dto = $serializer->deserialize($request->getContent(), StaticPageDto::class, 'json');

        $errors = $validator->validate($dto);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $entity = $repository->findAll();
        if (!\count($entity)) {
            $entity = new Site();
        } else {
            $entity = $entity[0];
        }

        $entity->{$page} = $dto->body;
        $entityManager->persist($entity);
        $entityManager->flush();

        return new JsonResponse(
            new SiteResponseDto($entity),
            headers: $headers
        );
    }
}
