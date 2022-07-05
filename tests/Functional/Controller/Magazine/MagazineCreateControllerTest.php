<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine;

use App\Tests\WebTestCase;

class MagazineCreateControllerTest extends WebTestCase
{
    public function testCanCreateMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $crawler = $client->request('GET', '/nowyMagazyn');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[name]' => 'acme',
                    'magazine[title]' => 'magazyn polityczny',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme');

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine h3', 'magazyn polityczny');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine', 'Subskrybujących: 1');
    }
}
