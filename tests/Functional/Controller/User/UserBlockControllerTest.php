<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\WebTestCase;

class UserBlockControllerTest extends WebTestCase
{
    public function testUserCanFollowOtherOnEntryPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        $client->submit($crawler->filter('#sidebar form[name=user_block] button')->form());

        $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=user_block] .active');
    }
}
