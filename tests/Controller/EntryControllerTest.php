<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class EntryControllerTest extends WebTestCase
{
    public function testCanCreateArticle()
    {
        $client  = static::createClient();

        $magazine = $this->getMagazineByName('polityka');

        $client->loginUser($this->getUserByUsername('user'));
        $crawler = $client->request('GET', '/nowaTresc/artykul');

        $client->submit($crawler->selectButton('Gotowe')->form([
            'entry_article[title]' => 'przykladowa tresc',
            'entry_article[body]' => 'Lorem ipsum',
            'entry_article[magazine]' => $magazine->getId()
        ]));

        self::assertResponseRedirects();

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.kbin-entry-title', 'przykladowa tresc');
    }

    public function testCanCreateLink()
    {
        $client  = static::createClient();

        $magazine = $this->getMagazineByName('polityka');

        $client->loginUser($this->getUserByUsername('user'));
        $crawler = $client->request('GET', '/nowaTresc');

        $client->submit($crawler->selectButton('Gotowe')->form([
            'entry_link[title]' => 'przykladowa tresc',
            'entry_link[url]' => 'https://example.pl',
            'entry_link[magazine]' => $magazine->getId()
        ]));

        self::assertResponseRedirects();

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.kbin-entry-title', 'przykladowa tresc');
    }
}
