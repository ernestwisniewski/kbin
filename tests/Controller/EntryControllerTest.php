<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class EntryControllerTest extends WebTestCase
{
    public function testCanCreateEntry()
    {
        $client  = static::createClient();

        $magazine = $this->getMagazineByName('polityka');

        $client->loginUser($this->getUserByUsername('user'));
        $crawler = $client->request('GET', '/nowyPost');

        $client->submit($crawler->selectButton('Zapisz')->form([
            'entry[title]' => 'przykladowa tresc',
            'entry[url]' => 'https://example.com',
            'entry[magazine]' => $magazine->getId()
        ]));

        self::assertResponseRedirects();

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'przykladowa tresc');
    }
}
