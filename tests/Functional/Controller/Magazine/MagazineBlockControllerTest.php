<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine;

use App\Tests\WebTestCase;

class MagazineBlockControllerTest extends WebTestCase
{
    public function testUserCanBlockMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());

        $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=magazine_block] .btn-warning');
    }
}
