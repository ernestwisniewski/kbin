<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\People;

use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class FrontControllerTest extends WebTestCase
{
    public function testFrontPeoplePage(): void
    {
        $client = $this->createClient();

        $user = $this->getUserByUsername('JohnDoe');

        $user->about = 'Loerm ipsum';
        $this->getService(EntityManagerInterface::class)->flush();

        $crawler = $client->request('GET', '/people');

        $this->assertEquals(1, $crawler->filter('#main .user-box')->count());
        $this->assertSelectorTextContains('#main .users .user-box', 'Loerm ipsum');
    }
}
