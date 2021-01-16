<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class MagazineControllerTest extends WebTestCase
{
    public function testCanCreateMagazine()
    {
        $client  = static::createClient();
        $client->loginUser($this->getRegularUser());
        $crawler = $client->request('GET', '/nowyMagazyn');

        $client->submit($crawler->selectButton('Zapisz')->form([
            'magazine[name]' => 'polityka',
            'magazine[title]' => 'magazyn polityczny',
        ]));

        self::assertResponseRedirects('/magazyny');

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Wszystkie magazyny');
        self::assertSelectorTextContains('li', 'polityka');
    }
}
