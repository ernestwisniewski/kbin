<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    /**
     * @dataProvider provider
     */
    public function testPageMenus($linkName)
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $this->loadFixtures();

        // Home page
        $crawler = $client->request('GET', '/magazyny');
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'Wszystkie');
        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

        // Sub
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item ')->selectLink('Obserwowane')->link());
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'Obserwowane');
        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

        // Mod
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item ')->selectLink('Moderowane')->link());
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'Moderowane');
        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

        // Magazine
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item ')->selectLink('polityka')->link());
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'polityka');
        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));
    }

    private function loadFixtures()
    {
        $user1     = $this->getUserByUsername('regularUser');
        $user2     = $this->getUserByUsername('regularUser2');
        $user3     = $this->getUserByUsername('regularUser3');
        $magazine  = $this->getMagazineByName('polityka', $user1);
        $magazine2 = $this->getMagazineByName('polityka2', $user2);
        $entry1    = $this->getEntryByTitle('entry1', null, 'treść 1', $magazine);
        $entry2    = $this->getEntryByTitle('entry2', null, 'treść 2', $magazine);
        $entry2    = $this->getEntryByTitle('entry3', null, 'treść 3', $magazine, $user3);
        $entry3    = $this->getEntryByTitle('entry4', null, 'treść 4', $magazine, $user2);

        $comment = $comment = $this->createEntryComment('przykładowy komentarz', $entry1);
    }

    public function testFrontPage()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez regularUser');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/polityka');
    }

    public function testSubPage()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item ')->selectLink('Obserwowane')->link());

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez regularUser');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/polityka');
    }

    public function testModPage()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item ')->selectLink('Moderowane')->link());

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez regularUser');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/polityka');
    }

    public function testCommentsPage()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink('Komentarze')->link());

        $this->assertSelectorTextContains('.kbin-comment-meta-user', 'przez regularUser');
        $this->assertSelectorTextContains('.kbin-comment-meta-magazine', 'do /m/polityka');
    }

    public function testMagazinePage()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item')->selectLink('polityka')->link());

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'regularUser');
        $this->assertSelectorNotExists('.kbin-entry-meta-magazine');
    }

    public function testMagazineCommentsPage()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item')->selectLink('polityka')->link());
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink('Komentarze')->link());

        $this->assertSelectorTextContains('.kbin-comment-meta-user', 'przez regularUser');
        $this->assertSelectorTextContains('.kbin-comment-meta-entry', 'w testowa treść');
        $this->assertSelectorNotExists('.kbin-comment-meta-magazine');
    }

    public function testUserEntryPage()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/u/regularUser');
        $crawler = $client->click($crawler->filter('.kbin-nav .kbin-nav-navbar-item ')->selectLink('Treści')->link());

        $this->assertSelectorNotExists('.kbin-entry-meta-user');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/polityka');
    }

    public function testUserCommentsPage()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/u/regularUser');
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar .kbin-nav-navbar-item')->selectLink('Komentarze')->link());

        $this->assertSelectorNotExists('.kbin-comments-meta-user');
        $this->assertSelectorTextContains('.kbin-comment-meta-magazine', 'do /m/polityka');
        $this->assertSelectorTextContains('.kbin-comment-meta-entry', 'w testowa treść');
    }

    public function provider()
    {
        return [
            ['Ważne'],
            ['Najnowsze'],
            ['Aktywne'],
            ['Wschodzące'],
            ['Komentowane'],
            ['Komentarze'],
        ];
    }
}
