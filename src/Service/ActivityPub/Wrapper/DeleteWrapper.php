<?php

declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Factory\ActivityPub\ActivityFactory;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeleteWrapper
{
    public function __construct(
        private readonly ActivityFactory $factory,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[ArrayShape([
        'id' => 'string',
        'type' => 'string',
        'object' => 'mixed',
        'actor' => 'mixed',
        'to' => 'mixed',
        'cc' => 'mixed',
    ])]
 public function build(ActivityPubActivityInterface $item, string $id): array
 {
     $item = $this->factory->create($item);

     return [
         '@context' => ActivityPubActivityInterface::CONTEXT_URL,
         'id' => $this->urlGenerator->generate('ap_object', ['id' => $id], UrlGeneratorInterface::ABSOLUTE_URL),
         'type' => 'Delete',
         'actor' => $item['attributedTo'],
         'object' => [
             'id' => $item['id'],
             'type' => 'Tombstone',
         ],
         'to' => $item['to'],
         'cc' => $item['cc'],
     ];
 }
}
