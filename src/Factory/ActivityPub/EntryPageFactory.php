<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Entry;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\ImageWrapper;
use App\Service\ActivityPub\Wrapper\MentionsWrapper;
use App\Service\ActivityPub\Wrapper\TagsWrapper;
use App\Service\ActivityPubManager;
use App\Service\ImageManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntryPageFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private GroupFactory $groupFactory,
        private ImageManager $imageManager,
        private ImageWrapper $imageWrapper,
        private TagsWrapper $tagsWrapper,
        private MentionsWrapper $mentionsWrapper,
        private ApHttpClient $client,
        private ActivityPubManager $activityPubManager,
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

        $tags = $entry->tags ?? [];
        if ($entry->magazine->name !== 'random') { // @todo
            $tags[] = $entry->magazine->name;
        }

        $page = array_merge($page ?? [], [
            'id' => $this->getActivityPubId($entry),
            'type' => 'Page',
            'attributedTo' => $this->activityPubManager->getActorProfileId($entry->user),
            'inReplyTo' => null,
            'to' => [
                $this->groupFactory->getActivityPubId($entry->magazine),
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc' => [
                $entry->apId
                    ? ($this->client->getActorObject($entry->user->apProfileId)['followers']) ?? []
                    : $this->urlGenerator->generate(
                    'ap_user_followers',
                    ['username' => $entry->user->username],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            'name' => $entry->title,
            'content' => $entry->body,
            'summary' => ($entry->body ? $entry->getShortDesc() : '').' '.implode(
                    ' ',
                    array_map(fn($val) => '#'.$val, $tags)
                ),
            'mediaType' => 'text/html',
            'url' => $this->getUrl($entry),
            'tag' => array_merge(
                $this->tagsWrapper->build($tags),
                $this->mentionsWrapper->build($entry->mentions ?? [], $entry->body)
            ),
            'commentsEnabled' => true,
            'sensitive' => $entry->isAdult(),
            'stickied' => $entry->sticky,
            'published' => $entry->createdAt->format(DATE_ATOM),
            'attachment' => [
//                [
//                    'href' => $this->getUrl($entry),
//                    'type' => 'Link',
//                ],
            ],
        ]);

        if ($entry->url) {
            $page['source'] = $entry->url;
        } else {
            if ($entry->image) {
                $page = $this->imageWrapper->build($page, $entry->image, $entry->title);
            }
        }

        if ($entry->body) {
            $page['to'] = array_unique(
                array_merge($page['to'], $this->activityPubManager->createCcFromBody($entry->body))
            );
        }

        return $page;
    }

    public function getActivityPubId(Entry $entry): string
    {
        if ($entry->apId) {
            return $entry->apId;
        }

        return str_replace(
            ['@'],
            '-',
            $this->urlGenerator->generate(
                'ap_entry',
                ['magazine_name' => $entry->magazine->name, 'entry_id' => $entry->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    private function getUrl(Entry $entry): string
    {
        if ($entry->type === Entry::ENTRY_TYPE_IMAGE) {
            return $this->imageManager->getUrl($entry->image);
        }

        return $entry->url ?? $this->getActivityPubId($entry);
    }
}
