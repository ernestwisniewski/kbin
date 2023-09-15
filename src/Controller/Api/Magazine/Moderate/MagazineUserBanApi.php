<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Moderate;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\DTO\MagazineBanDto;
use App\DTO\MagazineBanResponseDto;
use App\Entity\Magazine;
use App\Entity\User;
use App\Factory\MagazineFactory;
use App\Service\MagazineManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MagazineUserBanApi extends MagazineBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'User banned',
        content: new Model(type: MagazineBanResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'The ban\'s body was not formatted correctly',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not authorized to ban users from this magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'User or magazine not found',
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
        description: 'The magazine to ban the user in',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'user_id',
        in: 'path',
        description: 'The user to ban',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(content: new Model(type: MagazineBanDto::class))]
    #[OA\Tag(name: 'moderation/magazine')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine:ban:create'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE:BAN:CREATE')]
    #[IsGranted('moderate', subject: 'magazine')]
    /**
     * Create a new magazine ban for a user.
     */
    public function ban(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        #[MapEntity(id: 'user_id')]
        User $user,
        MagazineManager $manager,
        MagazineFactory $factory,
        SerializerInterface $deserializer,
        ValidatorInterface $validator,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        $moderator = $this->getUserOrThrow();
        /** @var MagazineBanDto $ban */
        $ban = $deserializer->deserialize($request->getContent(), MagazineBanDto::class, 'json');

        $errors = $validator->validate($ban);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $ban = $manager->ban($magazine, $user, $moderator, $ban);

        if (!$ban) {
            throw new BadRequestHttpException('Failed to ban user');
        }

        $response = $factory->createBanDto($ban);

        return new JsonResponse(
            $response,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'User unbanned',
        content: new Model(type: MagazineBanResponseDto::class),
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
        description: 'You are not authorized to unban this user',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'User or magazine not found',
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
        description: 'The magazine the user is banned in',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'user_id',
        in: 'path',
        description: 'The user to unban',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'moderation/magazine')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine:ban:delete'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE:BAN:DELETE')]
    #[IsGranted('moderate', subject: 'magazine')]
    /**
     * Remove magazine ban from a user.
     */
    public function unban(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        #[MapEntity(id: 'user_id')]
        User $user,
        MagazineManager $manager,
        MagazineFactory $factory,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $ban = $manager->unban($magazine, $user);

        if (!$ban) {
            throw new BadRequestHttpException('Failed to ban user');
        }

        $response = $factory->createBanDto($ban);

        return new JsonResponse(
            $response,
            headers: $headers
        );
    }
}
