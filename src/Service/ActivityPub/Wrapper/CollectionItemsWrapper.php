<?php

declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Contracts\ActivityPubActivityInterface;
use JetBrains\PhpStorm\ArrayShape;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CollectionItemsWrapper
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    #[ArrayShape([
        '@context' => 'string',
        'type' => 'string',
        'partOf' => 'string',
        'id' => 'string',
        'totalItems' => 'int',
        'orderedItems' => "\Pagerfanta\PagerfantaInterface",
        'next' => 'string',
    ])]
 public function build(
        string $routeName,
        array $routeParams,
        PagerfantaInterface $pagerfanta,
        array $items,
        int $page,
        ?array $context = null
    ): array {
     $result = [
         '@context' => $context ? array_merge([ActivityPubActivityInterface::CONTEXT_URL], [$context])
             : ActivityPubActivityInterface::CONTEXT_URL,
         'type' => 'OrderedCollectionPage',
         'partOf' => $this->urlGenerator->generate($routeName, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL),
         'id' => $this->urlGenerator->generate(
             $routeName,
             $routeParams + ['page' => $page],
             UrlGeneratorInterface::ABSOLUTE_URL
         ),
         'totalItems' => $pagerfanta->getNbResults(),
         'orderedItems' => $items,
     ];

     if ($pagerfanta->hasNextPage()) {
         $result['next'] = $this->urlGenerator->generate(
             $routeName,
             $routeParams + ['page' => $pagerfanta->getNextPage()],
             UrlGeneratorInterface::ABSOLUTE_URL
         );
     }

     return $result;
 }
}
