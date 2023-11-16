<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry;

use App\DTO\EntryRequestDto;
use App\DTO\EntryResponseDto;
use App\Entity\Entry;
use App\Factory\EntryFactory;
use App\Kbin\Entry\EntryEdit;
use App\Schema\Errors\ForbiddenErrorSchema;
use App\Schema\Errors\NotFoundErrorSchema;
use App\Schema\Errors\TooManyRequestsErrorSchema;
use App\Schema\Errors\UnauthorizedErrorSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntriesUpdateApi extends EntriesBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Entry updated',
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
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You do not have permission to update this entry',
        content: new OA\JsonContent(ref: new Model(type: ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Entry not found',
        content: new OA\JsonContent(ref: new Model(type: NotFoundErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: TooManyRequestsErrorSchema::class)),
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
        description: 'The id of the entry to update',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(content: new Model(
        type: EntryRequestDto::class,
        groups: [
            'common',
            Entry::ENTRY_TYPE_ARTICLE,
        ]
    ))]
    #[OA\Tag(name: 'entry')]
    #[Security(name: 'oauth2', scopes: ['entry:edit'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY:EDIT')]
    #[IsGranted('edit', subject: 'entry')]
    public function __invoke(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        EntryEdit $entryEdit,
        EntryFactory $entryFactory,
        ValidatorInterface $validator,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        $dto = $this->deserializeEntry($entryFactory->createDto($entry), context: [
            'groups' => [
                'common',
                Entry::ENTRY_TYPE_ARTICLE,
            ],
        ]);

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $entry = $entryEdit($entry, $dto);

        return new JsonResponse(
            $this->serializeEntry($entry),
            headers: $headers
        );
    }
}
