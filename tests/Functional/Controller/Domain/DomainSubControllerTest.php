<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Domain;

use App\Tests\WebTestCase;

class DomainSubControllerTest extends WebTestCase
{
    public function testUserCanSubscribeDomain(): void
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

        $client->submit($crawler->filter('#sidebar .domain')->selectButton('Subscribe')->form());

        $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=domain_subscribe] .active');
        $this->assertSelectorTextContains('#sidebar .domain', 'Unsubscribe');
        $this->assertSelectorTextContains('#sidebar .domain', '1');
    }
}
