<?php declare(strict_types=1);

namespace App\Tests\ApiDataProvider;


use App\DTO\MagazineDto;
use App\Tests\ApiTestCase;
use App\Tests\FactoryTrait;

class MagazineCollectionDataProviderTest extends ApiTestCase
{
    use FactoryTrait;

    public function testMagazineCollection(): void
    {
        $client = $this->createClient();

        $this->createEntryComment('test entry comment');
        $this->createPostComment('test post comment');

        $this->createMagazine('Magazine2', 'Magazine 2 title');
        $this->createMagazine('Magazine3', 'Magazine 3 title');

        $response = $client->request('GET', '/api/magazines');

        $this->assertCount(14, $response->toArray()['hydra:member'][0]);
        $this->assertCount(3, $response->toArray()['hydra:member'][0]['user']);

        $this->assertMatchesResourceCollectionJsonSchema(MagazineDto::class);

        $this->assertJsonContains([
            '@context'         => '/api/contexts/magazine',
            '@id'              => '/api/magazines',
            '@type'            => 'hydra:Collection',
            'hydra:member'     => [
                [
                    '@id'                => '/api/magazines/polityka',
                    '@type'              => 'magazine',
                    'user'               => [
                        '@id'      => '/api/users/regularUser',
                        '@type'    => 'user',
                        'username' => 'regularUser',
                    ],
                    'cover'              => null,
                    'name'               => 'polityka',
                    'title'              => 'Przykładowy magazyn',
                    'description'        => null,
                    'rules'              => null,
                    'subscriptionsCount' => 1,
                    'entryCount'         => 1,
                    'entryCommentCount'  => 1,
                    'postCount'          => 1,
                    'postCommentCount'   => 1,
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
