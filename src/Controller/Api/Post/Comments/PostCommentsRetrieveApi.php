<?php

declare(strict_types=1);

namespace App\Controller\Api\Post\Comments;

use App\Controller\Api\Post\PostsBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\PostComment\DTO\PostCommentResponseDto;
use App\PageView\PostCommentPageView;
use App\Repository\Criteria;
use App\Repository\PostCommentRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class PostCommentsRetrieveApi extends PostsBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns the Post Comment',
        content: new Model(type: PostCommentResponseDto::class),
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
        response: 404,
        description: 'Comment not found',
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
        name: 'comment_id',
        in: 'path',
        description: 'The post comment to retrieve',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'd',
        in: 'query',
        description: 'Comment tree depth to retrieve',
        schema: new OA\Schema(
            type: 'integer', default: self::DEPTH, minimum: self::MIN_DEPTH, maximum: self::MAX_DEPTH
        ),
    )]
    #[OA\Tag(name: 'post_comment')]
    public function __invoke(
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        PostCommentRepository $repository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $this->handlePrivateContent($comment);

        $repository->hydrate($comment);

        return new JsonResponse(
            $this->serializePostCommentTree($comment),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of post comments',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: PostCommentResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
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
        name: 'post_id',
        description: 'Post to retrieve comments from',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of comments to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'd',
        description: 'Max depth of comment tree to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: self::DEPTH, minimum: self::MIN_DEPTH, maximum: self::MAX_DEPTH)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of posts per page to retrieve',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: PostCommentRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'sort',
        description: 'Sort method to use when retrieving comments',
        in: 'path',
        schema: new OA\Schema(type: 'string', default: Criteria::SORT_HOT, enum: Criteria::SORT_OPTIONS)
    )]
    #[OA\Parameter(
        name: 'time',
        description: 'Max age of retrieved posts',
        in: 'path',
        schema: new OA\Schema(type: 'string', default: Criteria::TIME_ALL, enum: Criteria::TIME_ROUTES_EN)
    )]
    #[OA\Parameter(
        name: 'lang[]',
        description: 'Language(s) of comments to return',
        in: 'query',
        explode: true,
        allowReserved: true,
        schema: new OA\Schema(
            type: 'array',
            items: new OA\Items(type: 'string')
        )
    )]
    #[OA\Parameter(
        name: 'usePreferredLangs',
        description: 'Filter by a user\'s preferred languages? (Requires authentication and takes precedence over lang[])',
        in: 'query',
        schema: new OA\Schema(type: 'boolean', default: false),
    )]
    #[OA\Tag(name: 'post')]
    public function collection(
        #[MapEntity(id: 'post_id')]
        Post $post,
        PostCommentRepository $repository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;
        $criteria->sortOption = $criteria->resolveSort($request->get('sort', Criteria::SORT_HOT));
        $criteria->time = $criteria->resolveTime($request->get('time', Criteria::TIME_ALL));
        $criteria->perPage = self::constrainPerPage($request->get('perPage', PostCommentRepository::PER_PAGE));

        $this->handleLanguageCriteria($criteria);

        $comments = $repository->findByCriteria($criteria);

        $dtos = [];
        foreach ($comments->getCurrentPageResults() as $value) {
            \assert($value instanceof PostComment);
            try {
                $this->handlePrivateContent($value);
                array_push($dtos, $this->serializePostCommentTree($value));
            } catch (\Exception $e) {
                continue;
            }
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $comments),
            headers: $headers
        );
    }
}
