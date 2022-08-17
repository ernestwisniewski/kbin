<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Entry;
use App\Markdown\MarkdownConverter;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\ImageWrapper;
use App\Service\ActivityPub\Wrapper\MentionsWrapper;
use App\Service\ActivityPub\Wrapper\TagsWrapper;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use DateTimeInterface;

class EntryPageFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private GroupFactory $groupFactory,
        private SettingsManager $settings,
        private ImageWrapper $imageWrapper,
        private TagsWrapper $tagsWrapper,
        private MentionsWrapper $mentionsWrapper,
        private ApHttpClient $client,
        private ActivityPubManager $activityPubManager,
        private MarkdownConverter $markdownConverter
    ) {
    }

    public function create(Entry $entry, bool $context = false): array
    {
        if ($context) {
            $page['@context'] = [
                ActivityPubActivityInterface::CONTEXT_URL,
                ActivityPubActivityInterface::SECURITY_URL,
                PostNoteFactory::getContext(),
            ];
        }

        $body = $entry->body ?? $entry->getDescription();

        $page = array_merge($page ?? [], [
            'id'              => $this->getActivityPubId($entry),
            'type'            => 'Page',
            'attributedTo'    => $this->activityPubManager->getActorProfileId($entry->user),
            'inReplyTo'       => null,
            'to'              => [
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc'              => [
//                $this->groupFactory->getActivityPubId($entry->magazine),
                $entry->apId
                    ? $this->client->getActorObject($entry->user->apProfileId)['followers']
                    : $this->urlGenerator->generate('ap_user_followers', ['username' => $entry->user->username], UrlGeneratorInterface::ABS_URL),
            ],
            'name'            => $entry->title,
            'content'         => $this->markdownConverter->convertToHtml($body),
            'mediaType'       => 'text/html',
            'url'             => $this->getUrl($entry),
            'tag'             => $this->tagsWrapper->build($entry->tags) + $this->mentionsWrapper->build($entry->mentions),
            'commentsEnabled' => true,
            'sensitive'       => $entry->isAdult(),
            'stickied'        => $entry->sticky,
            'published'       => $entry->createdAt->format(DateTimeInterface::ISO8601),
            'attachment'      => [
//                [
//                    'href' => $this->getUrl($entry),
//                    'type' => 'Link',
//                ],
            ],
        ]);

        if ($entry->url) {
            $page['source'] = $entry->url;
        } else {
            $page = $this->imageWrapper->build($page, $entry->image, $entry->title);
        }

        if ($entry->body) {
            $page['cc'] = array_merge($page['cc'], $this->activityPubManager->createCcFromBody($entry->body));
        }

        return $page;
    }

    public function getActivityPubId(Entry $entry): string
    {
        if ($entry->apId) {
            return $entry->apId;
        }

        return $this->urlGenerator->generate(
            'ap_entry',
            ['magazine_name' => $entry->magazine->name, 'entry_id' => $entry->getId()],
            UrlGeneratorInterface::ABS_URL
        );
    }

    private function getUrl(Entry $entry): string
    {
        if ($entry->type === Entry::ENTRY_TYPE_IMAGE) {
            return 'https://'.$this->settings->get('KBIN_DOMAIN').'/media/'.$entry->image->filePath;
        }

        return $entry->url ?? $this->getActivityPubId($entry);
    }
}
