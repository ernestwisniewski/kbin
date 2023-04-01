<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine;

use App\Tests\WebTestCase;

class MagazineSubControllerTest extends WebTestCase
{
    public function testUserCanSubAndUnsubMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        // Sub magazine
        $client->submit($crawler->filter('#sidebar .magazine')->selectButton('Subscribe')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorExists('#sidebar form[name=magazine_subscribe] .active');
        $this->assertSelectorTextContains('#sidebar .magazine', 'Unsubscribe');
        $this->assertSelectorTextContains('#sidebar .magazine', '2');

        // Unsub magazine
        $client->submit($crawler->filter('#sidebar .magazine')->selectButton('Unsubscribe')->form());
        $client->followRedirect();
        $this->assertSelectorTextContains('#sidebar .magazine', '1');
    }

    public function testXmlUserCanSubMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar .magazine')->selectButton('Subscribe')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('Unsubscribe', $client->getResponse()->getContent());
    }

    public function testXmlUserCanUnsubMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');

        // Sub magazine
        $client->submit($crawler->filter('#sidebar .magazine')->selectButton('Subscribe')->form());
        $crawler = $client->followRedirect();

        // Unsub magazine
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->submit($crawler->filter('#sidebar .magazine')->selectButton('Unsubscribe')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
        $this->assertStringContainsString('Subscribe', $client->getResponse()->getContent());
    }
}
