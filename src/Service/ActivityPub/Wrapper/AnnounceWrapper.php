<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\Contracts\ActivityPubActivityInterface;
use DateTimeInterface;
use Symfony\Component\Uid\Uuid;

class AnnounceWrapper
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function build(
        string $user,
        array $object,
        \DateTimeInterface $createdAt
    ): array {
        $id = Uuid::v4()->toRfc4122();

        return [
            '@context'  => ActivityPubActivityInterface::CONTEXT_URL,
            'id'        => $this->urlGenerator->generate('ap_object', ['id' => $id], UrlGeneratorInterface::ABS_URL),
            'type'      => 'Announce',
            'actor'     => $user,
            'object'    => $object['id'],
            'to'        => ActivityPubActivityInterface::PUBLIC_URL,
            'cc'        => [$object['attributedTo']],
            'published' => $createdAt->format(DateTimeInterface::ISO8601),
        ];
    }
}
