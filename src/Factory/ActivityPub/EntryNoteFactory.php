<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\EntryComment;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class EntryNoteFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PersonFactory $personFactory,
        private GroupFactory $groupFactory,
        private RequestStack $requestStack
    ) {
    }

    public function create(EntryComment $comment): array
    {
        $note = [
            'type'         => 'Note',
            '@context'     => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id'           => $this->getActivityPubId($comment),
            'attributedTo' => $this->personFactory->getActivityPubId($comment->user),
            'to'           => [
                ActivityPubActivityInterface::PUBLIC_URL,
            ],
            'cc'           => [
                $this->groupFactory->getActivityPubId($comment->magazine),
                $this->personFactory->getActivityPubId($comment->entry->user),
            ],
            'content'      => $comment->body,
            'mediaType'    => 'text/html',
            'url'          => $this->getActivityPubId($comment),
            'inReplyTo'    => $this->groupFactory->getActivityPubId($comment->magazine),
            'published'    => $comment->createdAt->format(DateTimeInterface::ISO8601),
        ];

        if ($comment->image) {
            $note['image'] = [ // @todo icon?
                'type' => 'Image',
                'url'  => $this->requestStack->getCurrentRequest()->getUriForPath('/media/'.$comment->image->filePath) // @todo media url
            ];
        }

        return $note;
    }

    public function getActivityPubId(EntryComment $comment): string
    {
        return $this->urlGenerator->generate(
            'ap_entry_comment',
            ['magazine_name' => $comment->magazine->name, 'entry_id' => $comment->entry->getId(), 'comment_id' => $comment->getId()],
            UrlGeneratorInterface::ABS_URL
        );
    }
}
