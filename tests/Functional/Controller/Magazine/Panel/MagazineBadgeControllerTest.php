<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\Tests\WebTestCase;

class MagazineBadgeControllerTest extends WebTestCase
{
    public function testModCanAddAndRemoveBadge(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        // Add badge
        $crawler = $client->request('GET', '/m/acme/panel/badges');
        $this->assertSelectorTextContains('#main .options__main a.active', 'badges');
        $client->submit(
            $crawler->filter('#main form[name=badge]')->selectButton('Add badge')->form([
                'badge[name]' => 'test',
            ])
        );

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('#main .badges', 'test');

        // Remove badge
        $client->submit(
            $crawler->filter('#main .badges')->selectButton('Delete')->form()
        );

        $client->followRedirect();
        $this->assertSelectorTextContains('#main .section--muted', 'Empty');
    }

    public function testUnauthorizedUserCannotAddBadge(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/badges');

        $this->assertResponseStatusCodeSame(403);
    }
}
