<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;

class EntryBoostControllerTest extends WebTestCase
{
    public function testLoggedUserCanBoostEntry(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
            null,
            null,
            $this->getUserByUsername('JaneDoe')
        );

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->submit(
            $crawler->filter('#main .entry')->selectButton('boost')->form([])
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('#main .entry', 'boost (1)');

        $client->click($crawler->filter('#activity')->selectLink('boosts (1)')->link());

        $this->assertSelectorTextContains('#main .users-columns', 'JohnDoe');
    }
}
