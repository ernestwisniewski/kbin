<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\WebTestCase;

class UserFollowControllerTest extends WebTestCase
{
    public function testUserCanFollowOtherOnEntryPage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        $client->submit($crawler->filter('#sidebar .entry-info')->selectButton('Follow')->form());

        $client->followRedirect();

        $this->assertSelectorTextContains('#sidebar .entry-info', 'Unfollow');
    }
}
