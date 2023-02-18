<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Domain;

use App\Tests\WebTestCase;

class DomainBlockControllerTest extends WebTestCase
{
    public function testUserCanBlockMagazine(): void
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

        $client->submit($crawler->filter('#sidebar form[name=domain_block] button')->form());

        $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=domain_block] .btn-warning');
    }
}
