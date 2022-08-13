<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Factory\ActivityPub\ActivityFactory;
use JetBrains\PhpStorm\ArrayShape;

class CreateWrapper
{
    public function __construct(
        private ActivityFactory $factory,
    ) {
    }

    #[ArrayShape([
        'id'        => "string",
        'type'      => "string",
        'actor'     => "string",
        'published' => "string",
        'to'        => "array",
        'cc'        => "array",
        'object'    => "object",
    ])] public function build(ActivityPubActivityInterface $item): array
    {
        $item = $this->factory->create($item);

        return [
            'id'        => $item['id'],
            'type'      => 'Create',
            'actor'     => $item['attributedTo'],
            'published' => $item['published'],
            'to'        => $item['to'],
            'cc'        => $item['cc'],
            'object'    => $item,
        ];
    }
}
