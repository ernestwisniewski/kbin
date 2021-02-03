<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Repository\Criteria;
use App\Tests\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    /**
     * @dataProvider provider
     */
    public function testPageMenus($linkName) {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $this->loadFixtures();

        // Home page
        $crawler = $client->request('GET', '/magazyny');
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'Strona główna');
        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
//        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
//        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

        // Sub
//        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item ')->selectLink('Subskrybcje')->link());
//        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());
//
//        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'Subskrybcje');
//        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
//        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
//        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));
//
//        // Magazine
//        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item ')->selectLink('polityka')->link());
//        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());
//
//        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'polityka');
//        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
//        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
//        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

    }

    private function loadFixtures() {
        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');
        $magazine = $this->getMagazineByName('polityka', $user1);
        $magazine2 = $this->getMagazineByName('polityka2', $user2);
        $entry1 = $this->getEntryByTitle('entry1', null, 'treść 1', $magazine);
        $entry2 = $this->getEntryByTitle('entry2', null, 'treść 2', $magazine);
        $entry2 = $this->getEntryByTitle('entry3', null, 'treść 3', $magazine, $user3);
        $entry3 = $this->getEntryByTitle('entry4', null, 'treść 4', $magazine, $user2);

        $comment = $comment = $this->createEntryComment('przykładowy komentarz', $entry1);
    }

    public function provider() {
        return [
            ['Ważne'],
            ['Najnowsze'],
            ['Wchodzące'],
            ['Komentowane'],
            ['Komentarze']
        ];
    }
}
