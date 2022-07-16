<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\Post;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PostNoteFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PersonFactory $personFactory,
        private GroupFactory $groupFactory,
        private RequestStack $requestStack
    ) {
    }

    public function create(Post $post): array
    {
        $note = [
            'type'         => 'Note',
            '@context'     => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id'           => $this->getActivityPubId($post),
            'attributedTo' => $this->personFactory->getActivityPubId($post->user),
            'to'           => [
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc'           => [
                $this->groupFactory->getActivityPubId($post->magazine),
            ],
            'content'      => $post->body,
            'mediaType'    => 'text/html',
            'url'          => $this->getActivityPubId($post),
            'inReplyTo'    => $this->groupFactory->getActivityPubId($post->magazine),
            'published'    => $post->createdAt->format(DateTimeInterface::ISO8601),
        ];

        if ($post->image) {
            $note['image'] = [ // @todo icon?
                'type' => 'Image',
                'url'  => $this->requestStack->getCurrentRequest()->getUriForPath('/media/'.$post->image->filePath) // @todo media url
            ];
        }

        return $note;
    }

    public function getActivityPubId(Post $post): string
    {
        return $this->urlGenerator->generate(
            'ap_post',
            ['magazine_name' => $post->magazine->name, 'post_id' => $post->getId()],
            UrlGeneratorInterface::ABS_URL
        );
    }
}
