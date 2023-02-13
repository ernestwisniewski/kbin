<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiDataProvider;

use App\Tests\ApiTestCase;
use App\Tests\FactoryTrait;

class EntryCollectionDataProviderTest extends ApiTestCase
{
    use FactoryTrait;

    public function testEntriesCollection(): void
    {
        $client = $this->createClient();

        $entry = $this->createEntry('test1', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'), 'https://karab.in/');
        $this->createEntry('test2', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'), 'https://karab.in/');
        $this->createEntry('test3', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'), 'https://karab.in/');

        $this->createEntryComment('test entry comment', $entry);

        $this->createVote(1, $entry, $this->getUserByUsername('testUser1'));
        $this->createVote(-1, $entry, $this->getUserByUsername('testUser2'));

        $response = $client->request('GET', '/api/entries');

        $this->assertCount(19, $response->toArray()['hydra:member'][0]);
        $this->assertCount(3, $response->toArray()['hydra:member'][0]['user']);
        $this->assertCount(3, $response->toArray()['hydra:member'][0]['domain']);

//        $this->assertMatchesResourceCollectionJsonSchema(EntryDto::class); // todo image

        $this->assertJsonContains([
            '@context' => '/api/contexts/entry',
            '@id' => '/api/entries',
            '@type' => 'hydra:Collection',
            'hydra:member' => [
                [
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
                    ],
                    'image' => [
                        '@id' => '/api/images/'.$entry->image->getId(),
                        '@type' => 'image',
                        'filePath' => $entry->image->filePath,
                        'width' => 1280,
                        'height' => 1280,
                    ],
                    'domain' => [
                        '@id' => '/api/domains/'.$entry->domain->getId(),
                        '@type' => 'domain',
                        'name' => 'karab.in',
                    ],
                    'title' => 'test1',
                    'url' => 'https://karab.in/',
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
                ],
            ],
            'hydra:totalItems' => 3,
            'hydra:view' => [
                '@id' => '/api/entries?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/entries?page=1',
                'hydra:last' => '/api/entries?page=2',
                'hydra:next' => '/api/entries?page=2',
            ],
        ]);
    }
}
