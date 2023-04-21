<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\Tests\WebTestCase;

class MagazineModeratorControllerTest extends WebTestCase
{
    public function testOwnerCanAddAndRemoveModerator(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getUserByUsername('JaneDoe');
        $this->getMagazineByName('acme');

        // Add moderator
        $crawler = $client->request('GET', '/m/acme/panel/moderators');
        $this->assertSelectorTextContains('#main .options__main a.active', 'moderators');
        $crawler = $client->submit(
            $crawler->filter('#main form[name=moderator]')->selectButton('Add moderator')->form([
                'moderator[user]' => 'JaneDoe',
            ])
        );
        $this->assertSelectorTextContains('#main .users-columns', 'JaneDoe');
        $this->assertEquals(2, $crawler->filter('#main .users-columns ul li')->count());

        // Remove moderator
        $client->submit(
            $crawler->filter('#main .users-columns')->selectButton('Delete')->form()
        );

        $crawler = $client->followRedirect();
        $this->assertSelectorTextNotContains('#main .users-columns', 'JaneDoe');
        $this->assertEquals(1, $crawler->filter('#main .users-columns ul li')->count());
    }

    public function testUnauthorizedUserCannotAddModerator(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/moderators');

        $this->assertResponseStatusCodeSame(403);
    }
}
