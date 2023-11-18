<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Entry\Moderate;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Entity\Entry;
use App\Kbin\Entry\DTO\EntryResponseDto;
use App\Kbin\Entry\Factory\EntryFactory;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Intl\Languages;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntriesSetLanguageApi extends EntriesBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Entry language changed',
        content: new Model(type: EntryResponseDto::class),
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
        description: 'Given language is not valid',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not authorized to moderate this entry',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Entry not found',
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
        name: 'entry_id',
        in: 'path',
        description: 'The entry to change language of',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'lang',
        in: 'path',
        description: 'new language',
        schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 3),
    )]
    #[OA\Tag(name: 'moderation/entry')]
    #[Security(name: 'oauth2', scopes: ['moderate:entry:language'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:ENTRY:LANGUAGE')]
    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        EntityManagerInterface $manager,
        EntryFactory $factory,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        $newLang = $request->get('lang', '');

        $valid = false !== array_search($newLang, Languages::getLanguageCodes());

        if (!$valid) {
            throw new BadRequestHttpException('The given language is not valid!');
        }

        $entry->lang = $newLang;

        $manager->flush();

        return new JsonResponse(
            $this->serializeEntry($factory->createDto($entry)),
            headers: $headers
        );
    }
}
