<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;

class EntryCreateControllerTest extends WebTestCase
{
    public function testCanCreateArticle(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $magazine = $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/nowy/artykuł');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_article[title]'    => 'example content',
                    'entry_article[body]'     => 'Lorem ipsum',
                    'entry_article[magazine]' => $magazine->getId(),
                ]
            )
        );

        $this->assertResponseRedirects();

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-entry-title', 'example content');
    }

    public function testCanCreateLink(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $magazine = $this->getMagazineByName('acme');
        $this->getEntryByTitle('test1');
        $this->getEntryByTitle('test2');

        $crawler = $client->request('GET', '/nowy/link');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'example content',
                    'entry_link[url]'      => 'https://example.pl',
                    'entry_link[magazine]' => $magazine->getId(),
                    'entry_link[comment]'  => 'example comment',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $client->click($crawler->filter('.kbin-entry-title')->selectLink('example content')->link());

        $this->assertSelectorTextContains('.kbin-entry-title', 'example content');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Treści 3');
        $this->assertSelectorTextContains('.kbin-entry-meta-entry', '1 komentarz');
    }
}
