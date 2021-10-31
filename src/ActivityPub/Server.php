<?php declare(strict_types = 1);

namespace App\ActivityPub;

use ActivityPhp\Server as ActivityPub;
use ActivityPhp\Type;
use App\ActivityPub\Ontology\Mastodon;
use App\ActivityPub\Ontology\Peertube;
use App\ActivityPub\Ontology\Pleroma;
use App\ActivityPub\Type\PropertyValue;
use JetBrains\PhpStorm\NoReturn;

class Server
{
    public ActivityPub $server;

    public function __construct()
    {
        $this->server = new ActivityPub(
            [
                'ontologies' => [
                    'peertube' => Peertube::class,
                    'mastodon' => Mastodon::class,
                    'pleroma'  => Pleroma::class,
                ],
            ]
        );
        Type::add('PropertyValue', PropertyValue::class);
    }

    #[NoReturn] public function __invoke($actor)
    {
        $outbox = $this->server->outbox($actor);

        $pages[] = $outbox->getPage($outbox->get()->first);

        foreach ($pages as $page) {
            foreach ($page->orderedItems as $item) {
                dump($item);
            }
        }

        die;
    }
}
