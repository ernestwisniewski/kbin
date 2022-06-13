<?php declare(strict_types=1);

namespace App\Tests\Controller\Magazine\Panel;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MagazineEditControllerTest extends WebTestCase
{
    public function testCanEditMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/panel/edytuj');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[title]' => 'Przepisy kuchenne',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme');

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine h3', 'Przepisy kuchenne');
    }

    public function testCannotEditMagazineName(): void
    {
        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/panel/edytuj');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[name]' => 'kuchnia',
                    'magazine[title]' => 'Przepisy kuchenne',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-magazine-header .lead', 'acme');
        $this->assertSelectorTextNotContains('.kbin-magazine-header .lead', 'kuchnia');
    }

    public function testUnauthorizedUserCannotEditOrPurgeMagazine(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('secondUser'));
        $client->catchExceptions(false);

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/edytuj');

        $this->assertTrue($client->getResponse()->isForbidden());
    }
}
