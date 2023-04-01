<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\Tests\WebTestCase;

class MagazineBanControllerTest extends WebTestCase
{
    public function testModCanAddAndRemoveBan(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getUserByUsername('JaneDoe');
        $this->getMagazineByName('acme');

        // Add ban
        $crawler = $client->request('GET', '/m/acme/panel/bans');
        $this->assertSelectorTextContains('#main .options__main a.active', 'bans');
        $crawler = $client->submit(
            $crawler->filter('#main form[name=ban]')->selectButton('Add ban')->form([
                'username' => 'JaneDoe',
            ])
        );

        $client->submit(
            $crawler->filter('#main form[name=magazine_ban]')->selectButton('Ban')->form([
                'magazine_ban[reason]' => 'Reason test',
                'magazine_ban[expiredAt]' => (new \DateTime('+2 weeks'))->format('Y-m-d H:i:s'),
            ])
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('#main .bans-table', 'JaneDoe');

        // Remove ban
        $client->submit(
            $crawler->filter('#main .bans-table')->selectButton('Delete')->form()
        );

        $client->followRedirect();
        $this->assertSelectorTextContains('#main .bans-table', 'in 9 seconds');
    }

    public function testUnauthorizedUserCannotAddBan(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/bans');

        $this->assertResponseStatusCodeSame(403);
    }
}
