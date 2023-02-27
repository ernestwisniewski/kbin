<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Service\FavouriteManager;
use App\Tests\WebTestCase;

class EntryFavouriteControllerTest extends WebTestCase
{
    public function testUserCanSeeUpVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1');

        $manager = $client->getContainer()->get(FavouriteManager::class);
        $manager->toggle($this->getUserByUsername('JohnDoe'), $entry);
        $manager->toggle($this->getUserByUsername('JaneDoe'), $entry);

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->click($crawler->filter('.options-activity')->selectLink('favourites (2)')->link());

        $this->assertSelectorTextContains('#main .users-columns', 'JaneDoe');
        $this->assertSelectorTextContains('#main .users-columns', 'JohnDoe');
    }
}
