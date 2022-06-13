<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;

class EntryVotersControllerTest extends WebTestCase
{
    public function testCanSeeSidebarEntryVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('example');
        $this->createVote(1, $entry, $this->getUserByUsername('user1'));
        $this->createVote(1, $entry, $this->getUserByUsername('user2'));
        $this->createVote(1, $entry, $this->getUserByUsername('user3'));
        $this->createVote(1, $entry, $this->getUserByUsername('user4'));
        $this->createVote(2, $entry, $this->getUserByUsername('user5'));
        $this->createVote(2, $entry, $this->getUserByUsername('user6'));

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        $this->assertCount(6, $crawler->filter('.kbin-sidebar .kbin-voters ul li'));
    }

    public function testCanSeeEntryVotersPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('example');
        $this->createVote(1, $entry, $this->getUserByUsername('user1'));
        $this->createVote(1, $entry, $this->getUserByUsername('user2'));
        $this->createVote(1, $entry, $this->getUserByUsername('user3'));
        $this->createVote(1, $entry, $this->getUserByUsername('user4'));
        $this->createVote(2, $entry, $this->getUserByUsername('user5'));
        $this->createVote(2, $entry, $this->getUserByUsername('user6'));

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        $this->assertCount(6, $crawler->filter('.kbin-sidebar .kbin-voters ul li'));

        $crawler = $client->click($crawler->filter('.kbin-sidebar .kbin-voters ul li')->last()->filter('a')->link());

        $this->assertCount(6, $crawler->filter('.kbin-main .kbin-voters .card'));
    }

    public function testXmlUserCanSeeEntryVoters(): void
    {
        $client = $this->createClient();

        $magazine = $this->getMagazineByName('acme');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('JohnDoe');
        $user2 = $this->getUserByUsername('JaneDoe');

        $entry = $this->createEntry('entry test', $magazine, $user);

        $this->createVote(1, $entry, $user1);
        $this->createVote(1, $entry, $user2);

        $id = $entry->getId();
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', "/m/acme/t/$id/-/głosy");

        $this->assertStringContainsString('JaneDoe', $client->getResponse()->getContent());
    }
}
