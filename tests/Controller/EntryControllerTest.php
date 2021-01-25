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

        self::assertResponseRedirects("/m/polityka/t/{$entry->getId()}");

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
    }

    public function testCanEditArticle()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc', null, 'przykładowa treść wpisu');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/edytuj");

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_article[title]'    => 'zmieniona treść',
                    'entry_article[body]'     => 'zmieniona treść wpisu',
                    'entry_article[magazine]' => $entry->getMagazine()->getId(),
                ]
            )
        );

        self::assertResponseRedirects("/m/polityka/t/{$entry->getId()}");

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
        self::assertSelectorTextContains('p', 'zmieniona treść wpisu');
    }

    public function testCannotEditMagazine()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/edytuj");

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $this->getMagazineByName('test')->getId(),
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testPurgeEditArticle()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry  = $this->getEntryByTitle('przykladowa tresc', null, 'przykładowa treść wpisu');
        $entry2 = $this->getEntryByTitle('test', null, 'przykładowa treść wpisu');

        $this->createComment('test', $entry);

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/edytuj");

        $client->submit(
            $crawler->selectButton('Usuń')->form()
        );

        self::assertResponseRedirects("/m/polityka");

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('.kbin-entry-title', 'przykladowa tresc');
    }
}
