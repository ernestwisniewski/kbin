<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\WebTestCase;

class UserFollowControllerTest extends WebTestCase
{
    public function testUserCanFollowAndUnfollow(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        // Follow
        $client->submit($crawler->filter('#sidebar .entry-info')->selectButton('Follow')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=user_follow] .active');
        $this->assertSelectorTextContains('#sidebar .entry-info', 'Unfollow');
        $this->assertSelectorTextContains('#sidebar .entry-info', '1');

        // Unfollow
        $client->submit($crawler->filter('#sidebar .entry-info')->selectButton('Unfollow')->form());
        $client->followRedirect();

        $this->assertSelectorNotExists('#sidebar form[name=user_follow] .active');
        $this->assertSelectorTextContains('#sidebar .entry-info', 'Follow');
        $this->assertSelectorTextContains('#sidebar .entry-info', '0');
    }

    public function testXmlUserCanFollow(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar .entry-info')->selectButton('Follow')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('Unfollow', $client->getResponse()->getContent());
    }

    public function testXmlUserCanUnfollow(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        // Follow
        $client->submit($crawler->filter('#sidebar .entry-info')->selectButton('Follow')->form());
        $crawler = $client->followRedirect();

        // Unfollow
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar .entry-info')->selectButton('Unfollow')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('Follow', $client->getResponse()->getContent());
    }
}
