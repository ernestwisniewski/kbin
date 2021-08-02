<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\DTO\MagazineBanDto;
use App\Service\EntryCommentManagerInterface;
use App\Service\EntryManagerInterface;
use App\Service\MagazineManager;
use App\Service\PostCommentManagerInterface;
use App\Service\PostManagerInterface;
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
