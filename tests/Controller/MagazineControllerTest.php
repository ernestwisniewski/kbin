<?php declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;

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

        $this->assertResponseRedirects('/m/polityka');

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine h2', 'magazyn polityczny');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine', 'Subskrybujących: 1');
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

        $this->assertResponseRedirects('/m/polityka');

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine h2', 'Przepisy kuchenne');
    }

    public function testCannotEditMagazineName()
    {
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

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-magazine-header .lead', 'polityka');
        $this->assertSelectorTextNotContains('.kbin-magazine-header .lead', 'kuchnia');
    }

    public function testUnauthorizedUserCannotEditOrPurgeMagazine()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('secondUser'));
        $client->catchExceptions(false);

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/edytuj');

        $this->assertTrue($client->getResponse()->isForbidden());
    }

    public function testUserCanSubscribeMagazine()
    {
        $client  = $this->createClient();
        $manager = self::$container->get(MagazineManager::class);
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');

        $magazine  = $this->getMagazineByName('polityka', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);
        $magazine3 = $this->getMagazineByName('muzyka', $user2);

        $this->getEntryByTitle('treść 2', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine3, $user2);
        $this->getEntryByTitle('treść 4', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine3, $user);
        $this->getEntryByTitle('treść 1', null, null, $magazine, $user);

        $manager->subscribe($magazine, $user3);

        $crawler = $client->request('GET', '/m/polityka');

        $this->assertSelectorTextContains('.kbin-magazine-header .kbin-sub', '1');

        $client->submit(
            $crawler->filter('.kbin-magazine-header .kbin-sub')->selectButton('obserwuj')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-magazine-header .kbin-sub', '2');

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-entry-title', 'treść 1');
        $this->assertCount(3, $crawler->filter('.kbin-entry-title'));

        $crawler = $client->click($crawler->filter('.kbin-entry-title a')->link());

        $client->submit(
            $crawler->filter('.kbin-magazine-header .kbin-sub')->selectButton('obserwujesz')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-magazine-header .kbin-sub', '1');

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-entry-title', 'treść 1');
        $this->assertCount(2, $crawler->filter('.kbin-entry-title'));
    }
}
