<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiDataProvider;

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

        $response = $client->request('GET', '/api/magazines/acme');

        $this->assertCount(15, $response->toArray());

        $this->assertMatchesResourceItemJsonSchema(MagazineDto::class);

        $this->assertJsonEquals([
            '@context' => '/api/contexts/magazine',
            '@id' => '/api/magazines/acme',
            '@type' => 'magazine',
            'user' => [
                '@id' => '/api/users/JohnDoe',
                '@type' => 'user',
                'username' => 'JohnDoe',
                'avatar' => null,
            ],
            'icon' => null,
            'name' => 'acme',
            'title' => 'Magazine title',
            'description' => null,
            'rules' => null,
            'subscriptionsCount' => 1,
            'entryCount' => 1,
            'entryCommentCount' => 1,
            'postCount' => 1,
            'postCommentCount' => 1,
            'isAdult' => false,
        ]);
    }
}
