<?php

declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Post;
use App\Markdown\MarkdownConverter;
use App\Markdown\RenderTarget;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\ImageWrapper;
use App\Service\ActivityPub\Wrapper\MentionsWrapper;
use App\Service\ActivityPub\Wrapper\TagsWrapper;
use App\Service\ActivityPubManager;
use App\Service\MentionManager;
use App\Service\TagManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostNoteFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly GroupFactory $groupFactory,
        private readonly ImageWrapper $imageWrapper,
        private readonly TagsWrapper $tagsWrapper,
        private readonly MentionsWrapper $mentionsWrapper,
        private readonly ApHttpClient $client,
        private readonly ActivityPubManager $activityPubManager,
        private readonly MentionManager $mentionManager,
        private readonly TagManager $tagManager,
        private readonly MarkdownConverter $markdownConverter
    ) {
    }

    public function create(Post $post, bool $context = false): array
    {
        if ($context) {
            $note['@context'] = [
                ActivityPubActivityInterface::CONTEXT_URL,
                ActivityPubActivityInterface::SECURITY_URL,
                self::getContext(),
            ];
        }

        $tags = $post->tags ?? [];
        if ('random' !== $post->magazine->name && !$post->magazine->apId) { // @todo
            $tags[] = $post->magazine->name;
        }

        $note = array_merge($note ?? [], [
            'id' => $this->getActivityPubId($post),
            'type' => 'Note',
            'attributedTo' => $this->activityPubManager->getActorProfileId($post->user),
            'inReplyTo' => null,
            'to' => [
                $this->groupFactory->getActivityPubId($post->magazine),
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc' => [
                $post->apId
                    ? ($this->client->getActorObject($post->user->apProfileId)['followers']) ?? []
                    : $this->urlGenerator->generate(
                    'ap_user_followers',
                    ['username' => $post->user->username],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            'sensitive' => $post->isAdult(),
            'content' => $this->markdownConverter->convertToHtml(
                $this->tagManager->joinTagsToBody(
                    $post->body,
                    $tags
                ),
                [MarkdownConverter::RENDER_TARGET => RenderTarget::ActivityPub],
            ),
            'mediaType' => 'text/html',
            'url' => $this->getActivityPubId($post),
            'tag' => array_merge(
                $this->tagsWrapper->build($tags),
                $this->mentionsWrapper->build($post->mentions ?? [], $post->body)
            ),
            'commentsEnabled' => true,
            'published' => $post->createdAt->format(DATE_ATOM),
        ]);

        $note['contentMap'] = [
            $post->lang => $note['content'],
        ];

        if ($post->image) {
            $note = $this->imageWrapper->build($note, $post->image, $post->getShortTitle());
        }

        $note['to'] = array_unique(array_merge($note['to'], $this->activityPubManager->createCcFromBody($post->body)));

        return $note;
    }

    #[ArrayShape([
        'ostatus' => 'string',
        'sensitive' => 'string',
        'votersCount' => 'string',
    ])]
    public static function getContext(): array
    {
        return [
            'ostatus' => 'http://ostatus.org#',
            'sensitive' => 'as:sensitive',
            'votersCount' => 'toot:votersCount',
        ];
    }

    public function getActivityPubId(Post $post): string
    {
        if ($post->apId) {
            return $post->apId;
        }

        return $this->urlGenerator->generate(
            'ap_post',
            ['magazine_name' => $post->magazine->name, 'post_id' => $post->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
