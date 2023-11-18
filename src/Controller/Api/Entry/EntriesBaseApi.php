<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry;

use App\Controller\Api\BaseApi;
use App\DTO\ImageDto;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Kbin\Entry\DTO\EntryDto;
use App\Kbin\Entry\DTO\EntryRequestDto;
use App\Kbin\Entry\EntryCreate;
use App\Kbin\EntryComment\DTO\EntryCommentDto;
use App\Kbin\EntryComment\DTO\EntryCommentRequestDto;
use App\Kbin\EntryComment\DTO\EntryCommentResponseDto;
use App\Kbin\EntryComment\Factory\EntryCommentFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Service\Attribute\Required;

class EntriesBaseApi extends BaseApi
{
    private EntryCommentFactory $commentsFactory;

    #[Required]
    public function setCommentsFactory(EntryCommentFactory $commentsFactory)
    {
        $this->commentsFactory = $commentsFactory;
    }

    /**
     * Serialize a single entry to JSON.
     */
    protected function serializeEntry(EntryDto|Entry $dto)
    {
        $response = $this->entryFactory->createResponseDto($dto);

        if ($this->isGranted('ROLE_OAUTH2_ENTRY:VOTE')) {
            $response->isFavourited = $dto instanceof EntryDto ? $dto->isFavourited : $dto->isFavored(
                $this->getUserOrThrow()
            );
            $response->userVote = $dto instanceof EntryDto ? $dto->userVote : $dto->getUserChoice(
                $this->getUserOrThrow()
            );
        }

        return $response;
    }

    /**
     * Deserialize an entry from JSON.
     *
     * @param ?EntryDto $dto The EntryDto to modify with new values (default: null to create a new EntryDto)
     *
     * @return EntryDto An entry with only certain fields allowed to be modified by the user
     *
     * Modifies:
     *  * title
     *  * body
     *  * tags
     *  * isAdult
     *  * isOc
     *  * lang
     *  * imageAlt
     *  * imageUrl
     */
    protected function deserializeEntry(EntryDto $dto = null, array $context = []): EntryDto
    {
        $dto = $dto ? $dto : new EntryDto();
        $deserialized = $this->serializer->deserialize(
            $this->request->getCurrentRequest()->getContent(),
            EntryRequestDto::class,
            'json',
            $context
        );
        \assert($deserialized instanceof EntryRequestDto);

        $dto = $deserialized->mergeIntoDto($dto);

        $dto->ip = $this->ipResolver->resolve();

        return $dto;
    }

    protected function deserializeEntryFromForm(): EntryRequestDto
    {
        $request = $this->request->getCurrentRequest();
        $deserialized = new EntryRequestDto();
        $deserialized->title = $request->get('title');
        $deserialized->tags = $request->get('tags');
        // TODO: Support badges whenever/however they're implemented
        // $deserialized->badges = $request->get('badges');
        $deserialized->isOc = filter_var($request->get('isOc'), FILTER_VALIDATE_BOOL);
        $deserialized->lang = $request->get('lang');
        $deserialized->isAdult = filter_var($request->get('isAdult'), FILTER_VALIDATE_BOOL);

        return $deserialized;
    }

    /**
     * Serialize a single comment to JSON.
     */
    protected function serializeComment(EntryCommentDto $comment): EntryCommentResponseDto
    {
        $response = $this->entryCommentFactory->createResponseDto($comment);

        if ($this->isGranted('ROLE_OAUTH2_ENTRY_COMMENT:VOTE')) {
            $response->isFavourited = $comment->isFavourited;
            $response->userVote = $comment->userVote;
        }

        return $response;
    }

    /**
     * Deserialize a comment from JSON.
     *
     * @param ?EntryCommentDto $dto The EntryCommentDto to modify with new values (default: null to create a new EntryCommentDto)
     *
     * @return EntryCommentDto A comment with only certain fields allowed to be modified by the user
     *
     * Modifies:
     *  * body
     *  * isAdult
     *  * lang
     *  * imageAlt (currently not working to modify the image)
     *  * imageUrl (currently not working to modify the image)
     */
    protected function deserializeComment(EntryCommentDto $dto = null): EntryCommentDto
    {
        $dto = $dto ? $dto : new EntryCommentDto();

        /**
         * @var EntryCommentRequestDto $deserialized
         */
        $deserialized = $this->serializer->deserialize(
            $this->request->getCurrentRequest()->getContent(),
            EntryCommentRequestDto::class,
            'json',
            [
                'groups' => [
                    'common',
                    'comment',
                    'no-upload',
                ],
            ]
        );

        $dto->ip = $this->ipResolver->resolve();

        return $deserialized->mergeIntoDto($dto);
    }

    protected function deserializeCommentFromForm(EntryCommentDto $dto = null): EntryCommentDto
    {
        $request = $this->request->getCurrentRequest();
        $dto = $dto ? $dto : new EntryCommentDto();
        $deserialized = new EntryCommentRequestDto();

        $deserialized->body = $request->get('body');
        $deserialized->lang = $request->get('lang');

        $dto->ip = $this->ipResolver->resolve();

        return $deserialized->mergeIntoDto($dto);
    }

    /**
     * Serialize a comment tree to JSON.
     *
     * @param ?EntryComment $comment The root comment to base the tree on
     * @param ?int          $depth   how many levels of children to include. If null (default), retrieves depth from query parameter 'd'.
     *
     * @return array An associative array representation of the comment's hierarchy, to be used as JSON
     */
    protected function serializeCommentTree(?EntryComment $comment, int $depth = null): array
    {
        if (null === $comment) {
            return [];
        }

        if (null === $depth) {
            $depth = self::constrainDepth($this->request->getCurrentRequest()->get('d', self::DEPTH));
        }

        $commentTree = $this->commentsFactory->createResponseTree($comment, $depth);

        return $commentTree->jsonSerialize();
    }

    public function createEntry(
        Magazine $magazine,
        EntryCreate $entryCreate,
        array $context,
        ImageDto $image = null
    ): Entry {
        $dto = new EntryDto();
        $dto->magazine = $magazine;
        if (null !== $image) {
            $dto->image = $image;
        }

        if (null === $dto->magazine) {
            throw new NotFoundHttpException('Magazine not found');
        }

        $dto = $this->deserializeEntry($dto, $context);

        if (!$this->isGranted('create_content', $dto->magazine)) {
            throw new AccessDeniedHttpException();
        }

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        return $entryCreate($dto, $this->getUserOrThrow());
    }
}
