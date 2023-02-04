<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    public function testFrontPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry__meta', 'to acme');
    }

    public function testMagazinePage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/m/acme');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextNotContains('.kbin-entry__meta', 'to acme');
        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/m/acme');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-magazine', 'acme');
    }

//    public function testSubPage(): void
//    {
//        $client = $this->createClient();
//        $client->loginUser($this->getUserByUsername('JohnDoe'));
//
//        $this->getEntryByTitle('testowa treść');
//
//        $crawler = $client->request('GET', '/');
//        $crawler = $client->click($crawler->filter('.kbin-page-view-item')->selectLink('Obserwowane')->link());
//
//        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez JohnDoe');
//        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/acme');
//    }
//
//    public function testModPage(): void
//    {
//        $client = $this->createClient();
//        $client->loginUser($this->getUserByUsername('JohnDoe'));
//
//        $this->getEntryByTitle('testowa treść');
//
//        $crawler = $client->request('GET', '/');
//        $client->click($crawler->filter('.kbin-page-view-item')->selectLink('Moderowane')->link());
//
//        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'przez JohnDoe');
//        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/acme');
//    }
//
//    public function testCommentsPage(): void
//    {
//        $client = $this->createClient();
//        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));
//
//        $entry = $this->getEntryByTitle('testowa treść');
//        $this->createEntryComment('testowy komentarz', $entry, $user);
//
//        $crawler = $client->request('GET', '/');
//        $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink('Komentarze')->link());
//
//        $this->assertSelectorTextContains('.kbin-comment-meta-user', 'przez JohnDoe');
//        $this->assertSelectorTextContains('.kbin-comment-meta-magazine', 'do /m/acme');
//    }
//
//    public function testMagazinePage(): void
//    {
//        $client = $this->createClient();
//        $client->loginUser($this->getUserByUsername('JohnDoe'));
//
//        $this->getEntryByTitle('testowa treść');
//
//        $crawler = $client->request('GET', '/');
//        $client->click($crawler->filter('.kbin-featured-magazines-list-item')->selectLink('acme')->link());
//
//        $this->assertSelectorTextContains('.kbin-entry-meta-user', 'JohnDoe');
//        $this->assertSelectorNotExists('.kbin-entry-meta-magazine');
//    }
//
//    public function testMagazineCommentsPage(): void
//    {
//        $client = $this->createClient();
//        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));
//
//        $entry = $this->getEntryByTitle('testowa treść');
//        $this->createEntryComment('testowy komentarz', $entry, $user);
//
//        $crawler = $client->request('GET', '/');
//        $crawler = $client->click($crawler->filter('.kbin-featured-magazines-list-item')->selectLink('acme')->link());
//        $client->click($crawler->filter('.kbin-nav-navbar-item')->selectLink('Komentarze')->link());
//
//        $this->assertSelectorTextContains('.kbin-comment-meta-user', 'przez JohnDoe');
//        $this->assertSelectorTextContains('.kbin-comment-meta-entry', 'w testowa treść');
//        $this->assertSelectorNotExists('.kbin-comment-meta-magazine');
//    }
//
//    public function testUserEntryPage(): void
//    {
//        $client = $this->createClient();
//        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));
//
//        $entry = $this->getEntryByTitle('testowa treść');
//        $this->createEntryComment('testowy komentarz', $entry, $user);
//
//        $crawler = $client->request('GET', '/u/JohnDoe');
//        $client->click($crawler->filter('.kbin-nav .kbin-nav-navbar-item ')->selectLink('Treści')->link());
//
//        $this->assertSelectorNotExists('.kbin-entry-meta-user');
//        $this->assertSelectorTextContains('.kbin-entry-meta-magazine', 'do /m/acme');
//    }
//
//    public function testUserCommentsPage(): void
//    {
//        $client = $this->createClient();
//        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));
//
//        $entry = $this->getEntryByTitle('testowa treść');
//        $this->createEntryComment('testowy komentarz', $entry, $user);
//
//        $crawler = $client->request('GET', '/u/JohnDoe');
//        $client->click($crawler->filter('.kbin-nav-navbar .kbin-nav-navbar-item')->selectLink('Komentarze')->link());
//
//        $this->assertSelectorNotExists('.kbin-comments-meta-user');
//        $this->assertSelectorTextContains('.kbin-comment-meta-magazine', 'do /m/acme');
//        $this->assertSelectorTextContains('.kbin-comment-meta-entry', 'w testowa treść');
//    }
//
//    public function navbarProvider(): array
//    {
//        return [
//            ['Treści'],
//            ['Wpisy'],
//            ['Ludzie'],
//        ];
//    }
//
//    public function sortProvider(): array {
//        return [
//            ['Ważne'],
//            ['Gorące'],
//            ['Najnowsze'],
//            ['Aktywne'],
//            ['Komentowane'],
//        ];
//    }
}
