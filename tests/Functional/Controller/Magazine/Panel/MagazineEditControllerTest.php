<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\Tests\WebTestCase;

class MagazineEditControllerTest extends WebTestCase
{
    public function testModCanSeePanelLink():void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme');
        $this->assertSelectorTextContains('#sidebar .magazine', 'Magazine panel');
    }

    public function testOwnerCanEditMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/panel/general');
        $this->assertSelectorTextContains('#main .options__main a.active', 'general');
        $client->submit(
            $crawler->filter('#main form[name=magazine]')->selectButton('Done')->form([
                'magazine[description]' => 'test description edit',
                'magazine[rules]' => 'test rules edit',
                'magazine[isAdult]' => true,
            ])
        );

        $client->followRedirect();
        $this->assertSelectorTextContains('#sidebar .magazine', 'test description edit');
        $this->assertSelectorTextContains('#sidebar .magazine', 'test rules edit');
    }

    public function testUnauthorizedUserCannotEditMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/general');

        $this->assertResponseStatusCodeSame(403);
    }
}
