<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class EntryControllerTest extends WebTestCase
{
    public function testCanCreateArticle()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/nowaTresc/artykul');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_article[title]'    => 'przykladowa tresc',
                    'entry_article[body]'     => 'Lorem ipsum',
                    'entry_article[magazine]' => $magazine->getId(),
                ]
            )
        );

        self::assertResponseRedirects();

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.kbin-entry-title', 'przykladowa tresc');
    }

    public function testCanCreateLink()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/nowaTresc');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'przykladowa tresc',
                    'entry_link[url]'      => 'https://example.pl',
                    'entry_link[magazine]' => $magazine->getId(),
                ]
            )
        );

        self::assertResponseRedirects();

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.kbin-entry-title', 'przykladowa tresc');
    }

    public function testCanEditLink()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/edytuj");

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $entry->getMagazine()->getId(),
                ]
            )
        );

        self::assertResponseRedirects("/m/polityka/t/{$entry->getId()}/edytuj");

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertFormValue('[name=entry_link]', 'entry_link[title]', 'zmieniona treść');
        self::assertFormValue('[name=entry_link]', 'entry_link[url]', 'https://wp.pl');
    }
}
