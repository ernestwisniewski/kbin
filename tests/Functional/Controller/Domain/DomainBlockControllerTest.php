<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Domain;

use App\Tests\WebTestCase;

class DomainBlockControllerTest extends WebTestCase
{
    public function testUserCanBlockAndUnblockDomain(): void
    {
        $client = $this->createClient();

        $entry = $this->createEntry(
            'test entry 1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'http://kbin.pub/instances'
        );

        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', '/d/kbin.pub');

        // Block
        $client->submit($crawler->filter('#sidebar form[name=domain_block] button')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=domain_block] .active');

        // Unblock
        $client->submit($crawler->filter('#sidebar form[name=domain_block] button')->form());
        $client->followRedirect();

        $this->assertSelectorNotExists('#sidebar form[name=domain_block] .active');
    }

    public function testXmlUserCanBlockDomain(): void
    {
        $client = $this->createClient();

        $entry = $this->createEntry(
            'test entry 1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'http://kbin.pub/instances'
        );

        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', '/d/kbin.pub');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar form[name=domain_block] button')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('active', $client->getResponse()->getContent());
    }

    public function testXmlUserCanUnblockDomain(): void
    {
        $client = $this->createClient();

        $entry = $this->createEntry(
            'test entry 1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'http://kbin.pub/instances'
        );

        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', '/d/kbin.pub');

        // Block
        $client->submit($crawler->filter('#sidebar form[name=domain_block] button')->form());
        $crawler = $client->followRedirect();

        // Unblock
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar form[name=domain_block] button')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('active', $client->getResponse()->getContent());
    }
}
