<?php

declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Contracts\ActivityPubActivityInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class AnnounceWrapper
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function build(
        string $user,
        array $object,
    ): array {
        $id = Uuid::v4()->toRfc4122();

        return [
            '@context' => ActivityPubActivityInterface::CONTEXT_URL,
            'id' => $this->urlGenerator->generate('ap_object', ['id' => $id], UrlGeneratorInterface::ABSOLUTE_URL),
            'type' => 'Announce',
            'actor' => $user,
            'object' => $object['id'],
            'to' => [ActivityPubActivityInterface::PUBLIC_URL, $object['attributedTo']],
            'cc' => [],
            'published' => (new \DateTime())->format(DATE_ATOM),
        ];
    }
}
