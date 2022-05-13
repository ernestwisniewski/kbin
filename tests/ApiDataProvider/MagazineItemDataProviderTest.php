<?php declare(strict_types=1);

namespace App\Tests\ApiDataProvider;


use App\DTO\MagazineDto;
use App\Tests\ApiTestCase;
use App\Tests\FactoryTrait;

class MagazineItemDataProviderTest extends ApiTestCase
{
    use FactoryTrait;

    public function testMagazineItem(): void
    {
        $client = $this->createClient();

        $this->createEntryComment('test entry comment');
        $this->createPostComment('test post comment');

        $response = $client->request('GET', '/api/magazines/polityka');

        $this->assertCount(15, $response->toArray());

        $this->assertMatchesResourceItemJsonSchema(MagazineDto::class);

        $this->assertJsonEquals([
            '@context'           => '/api/contexts/magazine',
            '@id'                => '/api/magazines/polityka',
            '@type'              => 'magazine',
            'user'               => [
                '@id'      => '/api/users/regularUser',
                '@type'    => 'user',
                'username' => 'regularUser',
                'avatar'   => null,
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
        ]);

    }
}
