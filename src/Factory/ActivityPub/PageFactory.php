<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\Entry;
use Symfony\Component\HttpFoundation\RequestStack;

class PageFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PersonFactory $personFactory,
        private GroupFactory $groupFactory,
        private RequestStack $requestStack
    ) {
    }

    public function create(Entry $entry): array
    {
        $page = [
            'type'            => 'Page',
            '@context'        => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id'              => $this->getActivityPubId($entry),
            'attributedTo'    => $this->personFactory->getActivityPubId($entry->user),
            'to'              => [
                $this->groupFactory->getActivityPubId($entry->magazine),
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc'              => [],
            'name'            => $entry->title,
            'content'         => $entry->body ?? $entry->getDescription(),
            'url'             => $this->getActivityPubId($entry),
            'attachment'      => [
                'href' => $entry->url ?? $this->getActivityPubId($entry),
                'type' => 'Link',
            ],
            'commentsEnabled' => true,
            'sensitive'       => $entry->isAdult(),
            'stickied'        => $entry->sticky,
        ];

        if ($entry->image) {
            $page['image'] = [ // @todo icon?
                'type' => 'Image',
                'url'  => $this->requestStack->getCurrentRequest()->getUriForPath('/media/'.$entry->image->filePath) // @todo media url
            ];
        }


        return $page;
    }

    public function getActivityPubId(Entry $entry): string
    {
        return $this->urlGenerator->generate(
            'ap_entry',
            ['magazine_name' => $entry->magazine->name, 'entry_id' => $entry->getId()],
            UrlGeneratorInterface::ABS_URL
        );
    }
}
