<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Contracts\ActivityPubActivityInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CollectionInfoWrapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[ArrayShape([
        '@context'   => "string",
        'type'       => "string",
        'id'         => "string",
        'first'      => "string",
        'totalItems' => "int",
    ])] public function build(string $routeName, array $routeParams, int $count): array
    {
        return [
            '@context'   => ActivityPubActivityInterface::CONTEXT_URL,
            'type'       => 'OrderedCollection',
            'id'         => $this->urlGenerator->generate($routeName, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL),
            'first'      => $this->urlGenerator->generate(
                $routeName,
                $routeParams + ['page' => 1],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'totalItems' => $count,
        ];
    }
}
