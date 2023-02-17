<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Entity\Contracts\VoteInterface;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class EntryVotersControllerTest extends WebTestCase
{
    public function testUserCanSeeUpVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1');

        $manager = $client->getContainer()->get(VoteManager::class);
        $manager->vote(VoteInterface::VOTE_UP, $entry, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->click($crawler->filter('.options-activity')->selectLink('up votes (1)')->link());

        $this->assertSelectorTextContains('#main .user-list', 'JaneDoe');
    }

    public function testUserCanSeeDownVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1');

        $manager = $client->getContainer()->get(VoteManager::class);
        $manager->vote(VoteInterface::VOTE_DOWN, $entry, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->click($crawler->filter('.options-activity')->selectLink('down votes (1)')->link());

        $this->assertSelectorTextContains('#main .user-list', 'JaneDoe');
    }
}
