<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\PostComment;
use App\Service\ActivityPub\Wrapper\ImageWrapper;
use App\Service\ActivityPub\Wrapper\MentionsWrapper;
use App\Service\ActivityPub\Wrapper\TagsWrapper;
use DateTimeInterface;

class PostCommentNoteFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PersonFactory $personFactory,
        private GroupFactory $groupFactory,
        private PostNoteFactory $postNoteFactory,
        private ImageWrapper $imageWrapper,
        private TagsWrapper $tagsWrapper,
        private MentionsWrapper $mentionsWrapper
    ) {
    }

    public
    function create(
        PostComment $comment
    ): array {
        $note = [
            'type'         => 'Note',
            '@context'     => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id'           => $this->getActivityPubId($comment),
            'attributedTo' => $this->personFactory->getActivityPubId($comment->user),
            'inReplyTo'    => $comment->parent ? $this->getActivityPubId($comment->parent) : $this->postNoteFactory->getActivityPubId($comment->post),
            'to'           => [
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc'           => [
                $this->groupFactory->getActivityPubId($comment->magazine),
                $this->urlGenerator->generate('ap_user_followers', ['username' => $comment->user->username], UrlGeneratorInterface::ABS_URL),
            ],
            'content'      => $comment->body,
            'mediaType'    => 'text/html',
            'url'          => $this->getActivityPubId($comment),
            'tag'          => $this->tagsWrapper->build($comment->tags) + $this->mentionsWrapper->build($comment->mentions),
            'published'    => $comment->createdAt->format(DateTimeInterface::ISO8601),
        ];

        if ($comment->image) {
            $note = $this->imageWrapper->build($note, $comment->image, $comment->getShortTitle());
        }

        return $note;
    }

    public
    function getActivityPubId(
        PostComment $comment
    ): string {
        return $this->urlGenerator->generate(
            'ap_post_comment',
            ['magazine_name' => $comment->magazine->name, 'post_id' => $comment->post->getId(), 'comment_id' => $comment->getId()],
            UrlGeneratorInterface::ABS_URL
        );
    }
}
