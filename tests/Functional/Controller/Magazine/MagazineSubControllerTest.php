<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine;

use App\Tests\WebTestCase;

class MagazineSubControllerTest extends WebTestCase
{
    public function testUserCanSubscribeMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        $client->submit($crawler->filter('#sidebar .magazine')->selectButton('Subscribe')->form());

        $client->followRedirect();

        $this->assertSelectorTextContains('#sidebar .magazine', 'Unsubscribe');
    }
}
