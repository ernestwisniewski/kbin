<?php declare(strict_types=1);

namespace App\Tests\ApiDataProvider;


use App\Tests\ApiTestCase;
use App\Tests\FactoryTrait;

class MagazineCollectionDataProviderTest extends ApiTestCase
{
    use FactoryTrait;

    public function testMagazineCollection(): void
    {
        $client = $this->createClient();

        // @todo badges magazine
        $this->createMagazine('Magazine1', 'Magazine 1 title');
        $this->createMagazine('Magazine2', 'Magazine 2 title');
        $this->createMagazine('Magazine3', 'Magazine 3 title');

        $crawler = $client->request('GET', '/api/magazines');

        $this->assertJsonContains([
            '@context'         => '/api/contexts/magazine',
            '@id'              => '/api/magazines',
            '@type'            => 'hydra:Collection',
            'hydra:member'     => [
                [
                    '@id'                => '/api/magazines/Magazine1',
                    '@type'              => 'magazine',
                    'user'               => [
                        '@id'      => '/api/users/regularUser',
                        '@type'    => 'user',
                        'username' => 'regularUser',
                    ],
                    'cover'              => null,
                    'name'               => 'Magazine1',
                    'title'              => 'Magazine 1 title',
                    'description'        => null,
                    'rules'              => null,
                    'subscriptionsCount' => 1,
                    'entryCount'         => 0,
                    'entryCommentCount'  => 0,
                    'postCount'          => 0,
                    'postCommentCount'   => 0,
                    'isAdult'            => false,
                ],
            ],
            'hydra:totalItems' => 3,
            'hydra:view'       => [
                '@id'         => '/api/magazines?page=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/magazines?page=1',
                'hydra:last'  => '/api/magazines?page=2',
                'hydra:next'  => '/api/magazines?page=2',
            ],
        ]);

    }
}
