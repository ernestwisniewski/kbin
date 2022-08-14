<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Factory\ActivityPub\ActivityFactory;
use JetBrains\PhpStorm\ArrayShape;

class DeleteWrapper
{
    public function __construct(
        private ActivityFactory $factory,
    ) {
    }

    #[ArrayShape(['id'     => "string",
                  'type'   => "string",
                  'object' => "mixed",
                  'actor'  => "mixed",
                  'to'     => "mixed",
                  'cc'     => "mixed",
    ])] public function build(ActivityPubActivityInterface $item, string $id): array
    {
        $item = $this->factory->create($item);

        return [
            'id'     => $id,
            'type'   => 'Delete',
            'object' => $item['id'],
            'actor'  => $item['attributedTo'],
            'to'     => $item['to'],
            'cc'     => $item['cc'],
        ];
    }
}
