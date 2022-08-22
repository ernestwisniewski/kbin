<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Contracts\ActivityPubActivityInterface;
use DateTimeInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
            'id'        => $this->urlGenerator->generate('ap_object', ['id' => $id], UrlGeneratorInterface::ABSOLUTE_URL),
            'type'      => 'Announce',
            'actor'     => $user,
            'object'    => $object['id'],
            'to'        => [ActivityPubActivityInterface::PUBLIC_URL, $object['attributedTo']],
            'cc'        => [],
            'published' => $createdAt->format(DATE_ATOM),
        ];
    }
}
