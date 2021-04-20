<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\DTO\MagazineBanDto;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Tests\WebTestCase;

class ModlogControllerTest extends WebTestCase
{
    public function testModlog()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        $this->loadNotificationsFixture();

        $crawler = $client->request('GET', '/modlog');

        $this->assertCount(5, $crawler->filter('.table-responsive tr'));
    }
}
