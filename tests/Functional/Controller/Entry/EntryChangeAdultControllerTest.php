<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;

class EntryChangeAdultControllerTest extends WebTestCase
{
    public function testModCanMarkAsAdultContent(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
        );

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/moderate");
        $client->submit(
            $crawler->filter('.moderate-panel')->selectButton('18+ / nsfw')->form([
                'adult' => true,
            ])
        );
        $client->followRedirect();
        $this->assertSelectorTextContains('#main .entry .badge', '18+');

        $client->submit(
            $crawler->filter('.moderate-panel')->selectButton('18+ / nsfw')->form([
                'adult' => false,
            ])
        );
        $client->followRedirect();
        $this->assertSelectorTextNotContains('#main .entry', '18+');
    }
}
