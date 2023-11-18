<?php

declare(strict_types=1);

namespace App\Controller\Api\Post;

use App\Controller\Api\BaseApi;
use App\Entity\PostComment;
use App\Kbin\Post\DTO\PostDto;
use App\Kbin\Post\DTO\PostRequestDto;
use App\Kbin\Post\DTO\PostResponseDto;
use App\Kbin\PostComment\DTO\PostCommentDto;
use App\Kbin\PostComment\DTO\PostCommentRequestDto;
use App\Kbin\PostComment\DTO\PostCommentResponseDto;

class PostsBaseApi extends BaseApi
{
    /**
     * Serialize a single post to JSON.
     */
    protected function serializePost(PostDto $dto): PostResponseDto
    {
        if (null === $dto) {
            return [];
        }
        $response = $this->postFactory->createResponseDto($dto);

        if ($this->isGranted('ROLE_OAUTH2_POST:VOTE')) {
            $response->isFavourited = $dto instanceof PostDto ? $dto->isFavourited : $dto->isFavored(
                $this->getUserOrThrow()
            );
            $response->userVote = $dto instanceof PostDto ? $dto->userVote : $dto->getUserChoice(
                $this->getUserOrThrow()
            );
        }

        return $response;
    }

    /**
     * Deserialize a post from JSON.
     *
     * @param ?PostDto $dto The EntryDto to modify with new values (default: null to create a new PostDto)
     *
     * @return PostDto A post with only certain fields allowed to be modified by the user
     */
    protected function deserializePost(PostDto $dto = null): PostDto
    {
        $dto = $dto ? $dto : new PostDto();
        $deserialized = $this->serializer->deserialize(
            $this->request->getCurrentRequest()->getContent(),
            PostRequestDto::class,
            'json',
            [
                'groups' => [
                    'common',
                    'post',
                    'no-upload',
                ],
            ]
        );
        \assert($deserialized instanceof PostRequestDto);

        $dto = $deserialized->mergeIntoDto($dto);

        return $dto;
    }

    protected function deserializePostFromForm(PostDto $dto = null): PostDto
    {
        $request = $this->request->getCurrentRequest();
        $dto = $dto ? $dto : new PostDto();
        $deserialized = new PostRequestDto();
        $deserialized->body = $request->get('body');
        $deserialized->lang = $request->get('lang');
        $deserialized->isAdult = filter_var($request->get('isAdult'), FILTER_VALIDATE_BOOL);

        $dto = $deserialized->mergeIntoDto($dto);

        return $dto;
    }

    /**
     * Serialize a single comment to JSON.
     */
    protected function serializePostComment(PostCommentDto $comment): PostCommentResponseDto
    {
        $response = $this->postCommentFactory->createResponseDto($comment);

        if ($this->isGranted('ROLE_OAUTH2_POST_COMMENT:VOTE')) {
            $response->isFavourited = $comment instanceof PostCommentDto ? $comment->isFavourited : $comment->isFavored(
                $this->getUserOrThrow()
            );
            $response->userVote = $comment instanceof PostCommentDto ? $comment->userVote : $comment->getUserChoice(
                $this->getUserOrThrow()
            );
        }

        return $response;
    }

    /**
     * Deserialize a comment from JSON.
     *
     * @param ?PostCommentDto $dto The EntryCommentDto to modify with new values (default: null to create a new EntryCommentDto)
     *
     * @return PostCommentDto A comment with only certain fields allowed to be modified by the user
     *
     * Modifies:
     *  * body
     *  * isAdult
     *  * lang
     *  * imageAlt (currently not working to modify the image)
     *  * imageUrl (currently not working to modify the image)
     */
    protected function deserializePostComment(PostCommentDto $dto = null): PostCommentDto
    {
        $request = $this->request->getCurrentRequest();
        $dto = $dto ? $dto : new PostCommentDto();
        $deserialized = $this->serializer->deserialize($request->getContent(), PostCommentRequestDto::class, 'json', [
            'groups' => [
                'common',
                'comment',
                'no-upload',
            ],
        ]);

        \assert($deserialized instanceof PostCommentRequestDto);

        return $deserialized->mergeIntoDto($dto);
    }

    protected function deserializePostCommentFromForm(PostCommentDto $dto = null): PostCommentDto
    {
        $request = $this->request->getCurrentRequest();
        $dto = $dto ? $dto : new PostCommentDto();
        $deserialized = new PostCommentRequestDto();
        $deserialized->body = $request->get('body');
        $deserialized->lang = $request->get('lang');

        $dto = $deserialized->mergeIntoDto($dto);

        return $dto;
    }

    /**
     * Serialize a comment tree to JSON.
     *
     * @param ?PostComment $comment The root comment to base the tree on
     * @param ?int         $depth   how many levels of children to include. If null (default), retrieves depth from query parameter 'd'.
     *
     * @return array An associative array representation of the comment's hierarchy, to be used as JSON
     */
    protected function serializePostCommentTree(?PostComment $comment, int $depth = null): array
    {
        if (null === $comment) {
            return [];
        }

        if (null === $depth) {
            $depth = self::constrainDepth($this->request->getCurrentRequest()->get('d', self::DEPTH));
        }

        $commentTree = $this->postCommentFactory->createResponseTree($comment, $depth);

        return $commentTree->jsonSerialize();
    }
}
