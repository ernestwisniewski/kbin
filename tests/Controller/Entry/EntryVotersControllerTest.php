<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;

class EntryVotersControllerTest extends WebTestCase
{
    public function testCanSeeSidebarEntryVoters()
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

        $this->assertCount(6, $crawler->filter('.kbin-sidebar .kbin-voters ul li'));
    }

    public function testCanSeeEntryVotersPage()
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

        $this->assertCount(6, $crawler->filter('.kbin-sidebar .kbin-voters ul li'));

        $crawler = $client->click($crawler->filter('.kbin-sidebar .kbin-voters ul li')->last()->filter('a')->link());

        $this->assertCount(6, $crawler->filter('.kbin-main .kbin-voters .card'));
    }

    public function testXmlUserCanSeeEntryVoters()
    {
        $client = $this->createClient();

        $magazine = $this->getMagazineByName('polityka');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');

        $entry = $this->createEntry('entry test', $magazine, $user);

        $this->createVote(1, $entry, $user1);
        $this->createVote(1, $entry, $user2);

        $id = $entry->getId();
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', "/m/polityka/t/$id/-/głosy");

        $this->assertStringContainsString('regularUser2', $client->getResponse()->getContent());
    }
}
