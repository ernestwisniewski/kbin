<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class MagazineControllerTest extends WebTestCase
{
    public function testCanCreateMagazine()
    {
        $client  = static::createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $crawler = $client->request('GET', '/nowyMagazyn');

        $client->submit($crawler->selectButton('Zapisz')->form([
            'magazine[name]' => 'polityka',
            'magazine[title]' => 'magazyn polityczny',
        ]));

        self::assertResponseRedirects('/m/polityka');

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'magazyn polityczny');
    }
}
