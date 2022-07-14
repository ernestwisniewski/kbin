<?php declare(strict_types=1);

namespace App\ActivityPub;

use ActivityPhp\Server as ActivityPub;
use ActivityPhp\Type;
use ActivityPhp\Type\Validator;
use App\ActivityPub\Ontology\Lemmy;
use App\ActivityPub\Ontology\Mastodon;
use App\ActivityPub\Ontology\Peertube;
use App\ActivityPub\Ontology\Pleroma;
use App\ActivityPub\Type\Extended\Object\Emoji;
use App\ActivityPub\Type\Extended\Object\Infohash;
use App\ActivityPub\Type\Extended\Object\PropertyValue;
use App\ActivityPub\Type\Validator\NullableValidator;
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
                    'lemmy'    => Lemmy::class,
                ],
            ]
        );

        Type::add('PropertyValue', PropertyValue::class);
        Type::add('Infohash', Infohash::class);
        Type::add('Emoji', Emoji::class);

        // pleroma
        Validator::add('context', NullableValidator::class);
        Validator::add('endpoints', NullableValidator::class);
        Validator::add('source', NullableValidator::class);
        // soapbox
        Validator::add('href', NullableValidator::class);
        Validator::add('name', NullableValidator::class);
        // peertube
        Validator::add('height', NullableValidator::class);
        Validator::add('width', NullableValidator::class);
        Validator::add('url', NullableValidator::class);
    }

    public function create(): \ActivityPhp\Server
    {
        return $this->server;
    }

    #[NoReturn] public function __invoke($actor)
    {
//        $outbox = $this->server->outbox('harcesz@szmer.info');
//        $outbox = $this->server->outbox('lukas@peertube.pl');
//        $outbox = $this->server->outbox('random@armstube.com');
//        $outbox = $this->server->outbox('sp6ina@pleroma.s25.pl');
        $outbox = $this->server->outbox('mkljczk@pl.fediverse.pl');
//        $outbox = $this->server->outbox('kuba@toot.kuba-orlik.name');
//        $outbox = $this->server->actor('rdarmila@101010.pl')->webfinger();
//        $outbox = $this->server->outbox('rdarmila@101010.pl');

        $pages = [];

        $page    = $outbox->getPage($outbox->get()->first);
        $pages[] = $page;

        while ($page->next !== null) {
            $page    = $outbox->getPage($page->next);
            $pages[] = $page;
        }

        foreach ($pages as $page) {
            foreach ($page->orderedItems as $item) {
                if ($item->toArray()['object']) {
                    dump($item->toArray()['object']);
                }
            }
        }
        dd('end');
    }
}
