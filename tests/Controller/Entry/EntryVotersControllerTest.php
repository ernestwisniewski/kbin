<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;

class EntryVotersControllerTest extends WebTestCase
{
    public function testShowEntryVoters()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('example');
        $this->createVote(1, $entry, $this->getUserByUsername('user1'));
        $this->createVote(1, $entry, $this->getUserByUsername('user2'));
        $this->createVote(1, $entry, $this->getUserByUsername('user3'));
        $this->createVote(1, $entry, $this->getUserByUsername('user4'));
        $this->createVote(2, $entry, $this->getUserByUsername('user5'));
        $this->createVote(2, $entry, $this->getUserByUsername('user6'));

        $crawler = $client->request('GET', '/m/polityka/t/'.$entry->getId());

        $this->assertCount(5, $crawler->filter('.kbin-sidebar .kbin-users ul li'));
    }
}
