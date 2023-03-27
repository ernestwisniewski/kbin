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

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        $client->submit($crawler->filter('#sidebar .entry-info')->selectButton('Follow')->form());

        $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=user_follow] .active');
        $this->assertSelectorTextContains('#sidebar .entry-info', 'Unfollow');
        $this->assertSelectorTextContains('#sidebar .entry-info', '1');
    }
}
