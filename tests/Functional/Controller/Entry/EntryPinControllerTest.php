<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;

class EntryPinControllerTest extends WebTestCase
{
    public function testModCanPinEntry(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
        );

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/moderate");

        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('pin')->form([]));
        $crawler = $client->followRedirect();
        $this->assertSelectorExists('#main .entry .fa-thumbtack');

        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('unpin')->form([]));
        $client->followRedirect();
        $this->assertSelectorNotExists('#main .entry .fa-thumbtack');
    }
}
