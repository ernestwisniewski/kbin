<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class ModlogControllerTest extends WebTestCase
{
    public function testModlog(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        $this->loadNotificationsFixture();

        $crawler = $client->request('GET', '/modlog');

        $this->assertCount(5, $crawler->filter('.table-responsive tr'));
    }
}
