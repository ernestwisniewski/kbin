<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class MagazineControllerTest extends WebTestCase
{
    public function testCanCreateMagazine()
    {
        $client = static::createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $crawler = $client->request('GET', '/nowyMagazyn');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[name]'  => 'polityka',
                    'magazine[title]' => 'magazyn polityczny',
                ]
            )
        );

        self::assertResponseRedirects('/m/polityka');

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'magazyn polityczny');
    }

    public function testCanEditMagazine()
    {
        $client = static::createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/edytuj');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[name]'  => 'kuchnia',
                    'magazine[title]' => 'Przepisy kuchenne',
                ]
            )
        );

        self::assertResponseRedirects('/m/polityka/edytuj');

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertFormValue('[name=magazine]', 'magazine[name]', 'polityka');
        self::assertFormValue('[name=magazine]', 'magazine[title]', 'Przepisy kuchenne');
    }
}
