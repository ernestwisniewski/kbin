<?php declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class MagazinePanelControllerTest extends WebTestCase
{
    public function testCanEditMagazine()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/panel/edytuj');

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

        $crawler = $client->request('GET', '/m/polityka/panel/edytuj');

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

        $crawler = $client->request('GET', '/m/polityka/panel/edytuj');

        $this->assertTrue($client->getResponse()->isForbidden());
    }
}
