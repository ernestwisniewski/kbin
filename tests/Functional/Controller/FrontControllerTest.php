<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    /**
     * @dataProvider navbarProvider
     */
    public function testPageMenus($linkName): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $this->loadFixtures();

        // Home page
        $crawler = $client->request('GET', '/magazyny');
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-page-view-item--active', 'Wszystkie');
        $this->assertCount(1, $crawler->filter('.kbin-page-view-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

//        foreach ($this->sortProvider() as $sort) {
//            $crawler = $client->click($crawler->filter('.nav .nav-tabs')->selectLink($sort[0])->link());
//            $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'Wszystkie');
//            $this->assertSelectorTextContains('.nav .nav-tabs .nav-link', $sort[0]);
//            $this->assertCount(1, $crawler->filter('.active'));
//        }

        // Sub
        $crawler = $client->click($crawler->filter('.kbin-page-view-item')->selectLink('Obserwowane')->link());
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-page-view-item--active', 'Obserwowane');
        $this->assertCount(1, $crawler->filter('.kbin-page-view-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

        // Mod
        $crawler = $client->click($crawler->filter('.kbin-page-view-item')->selectLink('Moderowane')->link());
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-page-view-item--active', 'Moderowane');
        $this->assertCount(1, $crawler->filter('.kbin-page-view-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));

        // Magazine
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item')->selectLink('acme')->link());
        $crawler = $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink($linkName)->link());

        $this->assertSelectorTextContains('.kbin-featured-magazines-list-item--active', 'acme');
        $this->assertCount(1, $crawler->filter('.kbin-featured-magazines-list-item--active'));
        $this->assertSelectorTextContains('.kbin-nav-navbar-item--active', $linkName);
        $this->assertCount(1, $crawler->filter('.kbin-nav-navbar-item--active'));
    }

    private function loadFixtures(): void
    {
        $user1     = $this->getUserByUsername('JohnDoe');
        $user2     = $this->getUserByUsername('JaneDoe');
        $user3     = $this->getUserByUsername('MaryJane');
        $magazine  = $this->getMagazineByName('acme', $user1);
        $magazine2 = $this->getMagazineByName('acme2', $user2);
        $entry1    = $this->getEntryByTitle('entry1', null, 'content 1', $magazine);
        $entry2    = $this->getEntryByTitle('entry2', null, 'content 2', $magazine);
        $entry2    = $this->getEntryByTitle('entry3', null, 'content 3', $magazine, $user3);
        $entry3    = $this->getEntryByTitle('entry4', null, 'content 4', $magazine, $user2);

        $this->createEntryComment('example comment', $entry1);
    }

    public function testFrontPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/acme');
    }

    public function testSubPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-page-view-item')->selectLink('Obserwowane')->link());

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/acme');
    }

    public function testModPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('.kbin-page-view-item')->selectLink('Moderowane')->link());

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/acme');
    }

    public function testCommentsPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink('Komentarze')->link());

        $this->assertSelectorTextContains('.kbin-comment-meta-user', 'przez JohnDoe');
        $this->assertSelectorTextContains('.kbin-comment-meta-magazine', 'do /m/acme');
    }

    public function testMagazinePage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('.kbin-featured-magazines-list-item')->selectLink('acme')->link());

        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'JohnDoe');
        $this->assertSelectorNotExists('.kbin-entry-meta-magazine');
    }

    public function testMagazineCommentsPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item')->selectLink('acme')->link());
        $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink('Komentarze')->link());

        $this->assertSelectorTextContains('.kbin-comment-meta-user', 'przez JohnDoe');
        $this->assertSelectorTextContains('.kbin-comment-meta-entry', 'w testowa treść');
        $this->assertSelectorNotExists('.kbin-comment-meta-magazine');
    }

    public function testUserEntryPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/u/JohnDoe');
        $client->click($crawler->filter('.kbin-nav .kbin-nav-navbar-item ')->selectLink('Treści')->link());

        $this->assertSelectorNotExists('.kbin-entry-meta-user');
        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/acme');
    }

    public function testUserCommentsPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('testowa treść');
        $this->createEntryComment('testowy komentarz', $entry, $user);

        $crawler = $client->request('GET', '/u/JohnDoe');
        $client->click($crawler->filter('.kbin-nav-navbar .kbin-nav-navbar-item')->selectLink('Komentarze')->link());

        $this->assertSelectorNotExists('.kbin-comments-meta-user');
        $this->assertSelectorTextContains('.kbin-comment-meta-magazine', 'do /m/acme');
        $this->assertSelectorTextContains('.kbin-comment-meta-entry', 'w testowa treść');
    }

    public function navbarProvider(): array
    {
        return [
            ['Treści'],
            ['Komentarze'],
            ['Wpisy'],
        ];
    }

    public function sortProvider(): array {
        return [
            ['Ważne'],
            ['Gorące'],
            ['Najnowsze'],
            ['Aktywne'],
            ['Komentowane'],
        ];
    }
}
