<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\WebTestCase;

class UserBlockControllerTest extends WebTestCase
{
    public function testUserCanBlockAndUnblock(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        // Block
        $client->submit($crawler->filter('#sidebar form[name=user_block] button')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=user_block] .active');

        // Unblock
        $client->submit($crawler->filter('#sidebar form[name=user_block] button')->form());
        $client->followRedirect();

        $this->assertSelectorNotExists('#sidebar form[name=user_block] .active');
    }

    public function testXmlUserCanBlock(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        // Block
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar form[name=user_block] button')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('active', $client->getResponse()->getContent());
    }

    public function testXmlUserCanUnblock(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId());

        // Block
        $client->submit($crawler->filter('#sidebar form[name=user_block] button')->form());
        $crawler = $client->followRedirect();

        // Unblock
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar form[name=user_block] button')->form());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('active', $client->getResponse()->getContent());
    }
}
