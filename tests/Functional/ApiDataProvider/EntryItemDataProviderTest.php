<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiDataProvider;

use App\Tests\ApiTestCase;
use App\Tests\FactoryTrait;

class EntryItemDataProviderTest extends ApiTestCase
{
    use FactoryTrait;

    public function testEntryItem(): void
    {
        $client = $this->createClient();

        $entry = $this->createEntry(
            'test1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'https://karab.in/',
            null
        );
        $this->createEntry('test2', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'), 'https://karab.in/');
        $this->createEntry('test3', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'), 'https://karab.in/');

        $this->createEntryComment('test entry comment', $entry);

        $this->createVote(1, $entry, $this->getUserByUsername('testUser1'));
        $this->createVote(-1, $entry, $this->getUserByUsername('testUser2'));

        $response = $client->request('GET', '/api/entries/'.$entry->getId());

        $this->assertCount(21, $response->toArray());

//        $this->assertMatchesResourceItemJsonSchema(EntryDto::class); // @todo image

        $this->assertJsonEquals([
            '@context' => '/api/contexts/entry',
            '@id' => '/api/entries/'.$entry->getId(),
            '@type' => 'entry',
            'magazine' => [
                '@id' => '/api/magazines/acme',
                '@type' => 'magazine',
                'name' => 'acme',
            ],
            'user' => [
                '@id' => '/api/users/JohnDoe',
                '@type' => 'user',
                'username' => 'JohnDoe',
                'avatar' => null,
            ],
            'image' => null,
            'domain' => [
                '@id' => '/api/domains/'.$entry->domain->getId(),
                '@type' => 'domain',
                'name' => 'karab.in',
                'entryCount' => 3,
            ],
            'title' => 'test1',
            'url' => 'https://karab.in/',
            'body' => null,
            'comments' => 1,
            'uv' => 1,
            'dv' => 1,
            'isAdult' => false,
            'views' => 0,
            'score' => 0,
            'visibility' => 'visible',
            'createdAt' => $entry->createdAt->format(\DateTimeInterface::RFC3339),
            'lastActive' => $entry->lastActive->format(\DateTimeInterface::RFC3339),
            'id' => $entry->getId(),
            'type' => 'link',
        ]);
    }
}
