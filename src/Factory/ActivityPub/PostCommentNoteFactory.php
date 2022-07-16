<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\PostComment;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PostCommentNoteFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PersonFactory $personFactory,
        private GroupFactory $groupFactory,
        private RequestStack $requestStack
    ) {
    }

    public function create(PostComment $comment): array
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
                $this->personFactory->getActivityPubId($comment->post->user),
                $this->groupFactory->getActivityPubId($comment->magazine),
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

    public function getActivityPubId(PostComment $comment): string
    {
        return $this->urlGenerator->generate(
            'ap_post_comment',
            ['magazine_name' => $comment->magazine->name, 'post_id' => $comment->post->getId(), 'comment_id' => $comment->getId()],
            UrlGeneratorInterface::ABS_URL
        );
    }
}
