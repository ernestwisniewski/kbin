<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\PostComment;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\ImageWrapper;
use App\Service\ActivityPub\Wrapper\MentionsWrapper;
use App\Service\ActivityPub\Wrapper\TagsWrapper;
use App\Service\ActivityPubManager;
use DateTimeInterface;

class PostCommentNoteFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private GroupFactory $groupFactory,
        private PostNoteFactory $postNoteFactory,
        private ImageWrapper $imageWrapper,
        private TagsWrapper $tagsWrapper,
        private MentionsWrapper $mentionsWrapper,
        private ApHttpClient $client,
        private ActivityPubManager $activityPubManager,
    ) {
    }

    public function create(PostComment $comment, bool $context = false): array
    {
        if ($context) {
            $note['@context'] = [
                ActivityPubActivityInterface::CONTEXT_URL,
                ActivityPubActivityInterface::SECURITY_URL,
                PostNoteFactory::getContext(),
            ];
        }

        $note = array_merge($note ?? [], [
            'id'           => $this->getActivityPubId($comment),
            'type'         => 'Note',
            'attributedTo' => $this->activityPubManager->getActorProfileId($comment->user),
            'inReplyTo'    => $this->getReplyTo($comment),
            'to'           => [
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc'           => [
//                $this->groupFactory->getActivityPubId($comment->magazine),
                $comment->apId
                    ? $this->client->getActorObject($comment->user->apProfileId)['followers']
                    : $this->urlGenerator->generate('ap_user_followers', ['username' => $comment->user->username], UrlGeneratorInterface::ABS_URL),
            ],
            'sensitive'    => $comment->post->isAdult(),
            'content'      => $comment->body,
            'mediaType'    => 'text/html',
            'url'          => $this->getActivityPubId($comment),
            'tag'          => $this->tagsWrapper->build($comment->tags) + $this->mentionsWrapper->build($comment->mentions),
            'published'    => $comment->createdAt->format(DateTimeInterface::ISO8601),
        ]);

        if ($comment->image) {
            $note = $this->imageWrapper->build($note, $comment->image, $comment->getShortTitle());
        }

        $note['cc'] = array_merge($note['cc'], $this->activityPubManager->createCcFromBody($comment->body));

        return $note;
    }

    public function getActivityPubId(PostComment $comment): string
    {
        if ($comment->apId) {
            return $comment->apId;
        }

        return $this->urlGenerator->generate(
            'ap_post_comment',
            ['magazine_name' => $comment->magazine->name, 'post_id' => $comment->post->getId(), 'comment_id' => $comment->getId()],
            UrlGeneratorInterface::ABS_URL
        );
    }

    private function getReplyTo(PostComment $comment): string
    {
        if ($comment->apId) {
            return $comment->apId;
        }

        return $comment->parent ? $this->getActivityPubId($comment->parent) : $this->postNoteFactory->getActivityPubId($comment->post);
    }
}
