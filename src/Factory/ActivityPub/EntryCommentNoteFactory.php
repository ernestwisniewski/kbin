<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\EntryComment;
use App\Markdown\MarkdownConverter;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\ImageWrapper;
use App\Service\ActivityPub\Wrapper\MentionsWrapper;
use App\Service\ActivityPub\Wrapper\TagsWrapper;
use App\Service\ActivityPubManager;
use App\Service\MentionManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntryCommentNoteFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private GroupFactory $groupFactory,
        private ImageWrapper $imageWrapper,
        private TagsWrapper $tagsWrapper,
        private MentionsWrapper $mentionsWrapper,
        private MentionManager $mentionManager,
        private EntryPageFactory $pageFactory,
        private ApHttpClient $client,
        private ActivityPubManager $activityPubManager,
        private MarkdownConverter $markdownConverter
    ) {
    }

    public function create(EntryComment $comment, bool $context = false): array
    {
        if ($context) {
            $note['@context'] = [
                ActivityPubActivityInterface::CONTEXT_URL,
                ActivityPubActivityInterface::SECURITY_URL,
                PostNoteFactory::getContext(),
            ];
        }

        $tags = $comment->tags ?? [];
        if ($comment->magazine->name !== 'random') { // @todo
            $tags[] = $comment->magazine->name;
        }

        $note = [
            'type' => 'Note',
            '@context' => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id' => $this->getActivityPubId($comment),
            'attributedTo' => $this->activityPubManager->getActorProfileId($comment->user),
            'inReplyTo' => $this->getReplyTo($comment),
            'to' => [
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc' => [
//                $this->groupFactory->getActivityPubId($comment->magazine),
                $comment->apId
                    ? ($this->client->getActorObject($comment->user->apProfileId)['followers']) ?? []
                    : $this->urlGenerator->generate(
                    'ap_user_followers',
                    ['username' => $comment->user->username],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            'content' => $this->markdownConverter->convertToHtml(
                $this->mentionManager->joinMentionsToBody($comment->body ?? '', $comment->mentions ?? []),
            ),
            'mediaType' => 'text/html',
            'url' => $this->getActivityPubId($comment),
            'tag' => array_merge(
                $this->tagsWrapper->build($tags),
                $this->mentionsWrapper->build($comment->mentions ?? [], $comment->body)
            ),
            'published' => $comment->createdAt->format(DATE_ATOM),
        ];

        if ($comment->image) {
            $note = $this->imageWrapper->build($note, $comment->image, $comment->getShortTitle());
        }

        $mentions = [];
        foreach ($comment->mentions ?? [] as $mention) {
            try {
                $mentions[] = $this->activityPubManager->webfinger($mention)->getProfileId();
            } catch (\Exception $e) {
                continue;
            }
        }

        $note['to'] = array_values(
            array_unique(
                array_merge(
                    $note['to'],
                    $mentions,
                    $this->activityPubManager->createCcFromBody($comment->body),
                    [$this->getReplyToAuthor($comment)],
                )
            )
        );

        return $note;
    }

    public function getActivityPubId(EntryComment $comment): string
    {
        if ($comment->apId) {
            return $comment->apId;
        }

        return $this->urlGenerator->generate(
            'ap_entry_comment',
            [
                'magazine_name' => $comment->magazine->name,
                'entry_id' => $comment->entry->getId(),
                'comment_id' => $comment->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    private function getReplyTo(EntryComment $comment): string
    {
        if ($comment->apId) {
            return $comment->apId;
        }

        return $comment->parent ? $this->getActivityPubId($comment->parent) : $this->pageFactory->getActivityPubId(
            $comment->entry
        );
    }

    private function getReplyToAuthor(EntryComment $comment): string
    {
        return $comment->parent
            ? $this->activityPubManager->getActorProfileId($comment->parent->user)
            : $this->activityPubManager->getActorProfileId($comment->entry->user);
    }
}
