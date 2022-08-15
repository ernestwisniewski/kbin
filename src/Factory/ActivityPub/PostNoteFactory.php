<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Post;
use App\Markdown\MarkdownConverter;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\ImageWrapper;
use App\Service\ActivityPub\Wrapper\MentionsWrapper;
use App\Service\ActivityPub\Wrapper\TagsWrapper;
use App\Service\ActivityPubManager;
use DateTimeInterface;
use JetBrains\PhpStorm\ArrayShape;

class PostNoteFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private GroupFactory $groupFactory,
        private ImageWrapper $imageWrapper,
        private TagsWrapper $tagsWrapper,
        private MentionsWrapper $mentionsWrapper,
        private ApHttpClient $client,
        private ActivityPubManager $activityPubManager,
        private MarkdownConverter $markdownConverter
    ) {
    }

    public function create(Post $post, bool $context = false): array
    {
        if ($context) {
            $note['@context'] = [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL, self::getContext()];
        }

        $note = array_merge($note ?? [], [
            'id'              => $this->getActivityPubId($post),
            'type'            => 'Note',
            'attributedTo'    => $this->activityPubManager->getActorProfileId($post->user),
            'inReplyTo'       => null,
            'to'              => [
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc'              => [
//                $this->groupFactory->getActivityPubId($post->magazine),
                $post->apId
                    ? $this->client->getActorObject($post->user->apProfileId)['followers']
                    : $this->urlGenerator->generate(
                    'ap_user_followers',
                    ['username' => $post->user->username],
                    UrlGeneratorInterface::ABS_URL
                ),
            ],
            'sensitive'       => $post->isAdult(),
            'content'         => $this->markdownConverter->convertToHtml($post->body),
            'mediaType'       => 'text/html',
            'url'             => $this->getActivityPubId($post),
            'tag'             => $this->tagsWrapper->build($post->tags) + $this->mentionsWrapper->build($post->mentions),
            'commentsEnabled' => true,
            'published'       => $post->createdAt->format(DateTimeInterface::ISO8601),
        ]);

        if ($post->image) {
            $note = $this->imageWrapper->build($note, $post->image, $post->getShortTitle());
        }

        $note['cc'] = array_merge($note['cc'], $this->activityPubManager->createCcFromBody($post->body));

        return $note;
    }

    #[ArrayShape(['ostatus' => "string", 'sensitive' => "string", 'votersCount' => "string"])] public static function getContext(): array
    {
        return [
            'ostatus'     => 'http://ostatus.org#',
            'sensitive'   => 'as:sensitive',
            'votersCount' => 'toot:votersCount',
        ];
    }

    public function getActivityPubId(Post $post): string
    {
        if ($post->apId) {
            return $post->apId;
        }

        return $post->apId ?? $this->urlGenerator->generate(
                'ap_post',
                ['magazine_name' => $post->magazine->name, 'post_id' => $post->getId()],
                UrlGeneratorInterface::ABS_URL
            );
    }
}
