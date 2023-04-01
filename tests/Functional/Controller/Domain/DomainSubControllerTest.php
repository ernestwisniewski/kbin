<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Domain;

use App\Tests\WebTestCase;

class DomainSubControllerTest extends WebTestCase
{
    public function testUserCanSubAndUnsubDomain(): void
    {
        $client = $this->createClient();

        $this->createEntry(
            'test entry 1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'http://kbin.pub/instances'
        );

        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', '/d/kbin.pub');

        // Subscribe
        $client->submit($crawler->filter('#sidebar .domain')->selectButton('Subscribe')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=domain_subscribe] .active');
        $this->assertSelectorTextContains('#sidebar .domain', 'Unsubscribe');
        $this->assertSelectorTextContains('#sidebar .domain', '1');

        // Unsubscribe
        $client->submit($crawler->filter('#sidebar .domain')->selectButton('Unsubscribe')->form());
        $client->followRedirect();

        $this->assertSelectorNotExists('#sidebar form[name=domain_subscribe] .active');
        $this->assertSelectorTextContains('#sidebar .domain', 'Subscribe');
        $this->assertSelectorTextContains('#sidebar .domain', '0');
    }

    public function testXmlUserCanSubDomain(): void
    {
        $client = $this->createClient();

        $this->createEntry(
            'test entry 1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'http://kbin.pub/instances'
        );

        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', '/d/kbin.pub');

        // Subscribe
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar .domain')->selectButton('Subscribe')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('Unsubscribe', $client->getResponse()->getContent());
    }

    public function testXmlUserCanUnsubDomain(): void
    {
        $client = $this->createClient();

        $this->createEntry(
            'test entry 1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'http://kbin.pub/instances'
        );

        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', '/d/kbin.pub');

        // Subscribe
        $client->submit($crawler->filter('#sidebar .domain')->selectButton('Subscribe')->form());
        $crawler = $client->followRedirect();

        // Unsubscribe
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar .domain')->selectButton('Unsubscribe')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('Subscribe', $client->getResponse()->getContent());
    }
}
