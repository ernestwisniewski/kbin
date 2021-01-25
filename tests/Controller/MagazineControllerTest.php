<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MagazineControllerTest extends WebTestCase
{
    public function testCanCreateMagazine()
    {
        $client = $this->createClient();
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
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/edytuj');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[title]' => 'Przepisy kuchenne',
                ]
            )
        );

        self::assertResponseRedirects('/m/polityka');

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Przepisy kuchenne');
    }

    public function testCannotEditMagazineName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
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

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testCanPurgeMagazine()
    {
        $this->expectException(NotFoundHttpException::class);

        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));
        $client->catchExceptions(false);

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/edytuj');

        $client->submit(
            $crawler->selectButton('Usuń')->form()
        );

        self::assertResponseRedirects('/');

        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();

        $crawler = $client->request('GET', '/m/polityka');

        $this->assertTrue($client->getResponse()->isNotFound());
    }
}
