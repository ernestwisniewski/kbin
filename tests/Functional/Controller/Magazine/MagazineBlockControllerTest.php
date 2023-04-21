<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine;

use App\Tests\WebTestCase;

class MagazineBlockControllerTest extends WebTestCase
{
    public function testUserCanBlockAndUnblockMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        // Block magazine
        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());
        $crawler = $client->followRedirect();
        $this->assertSelectorExists('#sidebar form[name=magazine_block] .active');

        // Unblock magazine
        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());
        $client->followRedirect();
        $this->assertSelectorNotExists('#sidebar form[name=magazine_block] .active');
    }

    public function testXmlUserCanBlockMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('active', $client->getResponse()->getContent());
    }

    public function testXmlUserCanUnblockMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        // Block magazine
        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());
        $crawler = $client->followRedirect();

        // Unblock magazine
        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar form[name=magazine_block] button')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('active', $client->getResponse()->getContent());
    }
}
