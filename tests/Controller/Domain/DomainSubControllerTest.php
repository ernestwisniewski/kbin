<?php declare(strict_types=1);

namespace App\Tests\Controller\Domain;

use App\Tests\WebTestCase;

class DomainSubControllerTest extends WebTestCase
{
    use DomainFixturesTrait;

    public function testDomainSubAndUnsubController(): void
    {
        $client = static::createClient();

        $client->loginUser($this->getUserByUsername('testUser'));

        $this->createEntryFixtures();

        $crawler = $client->request('GET', '/d/karab.in');

        // subscribe
        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('obserwuj')->form()
        );

        $client->followRedirect();

        $this->assertSelectorExists('.kbin-sub--active');

        $crawler = $client->request('GET', '/sub');

        $this->assertEquals(2, $crawler->filter('.kbin-entry-title-domain')->count());

        $crawler = $client->request('GET', '/d/karab.in');

        // usubscribe
        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('obserwuj')->form()
        );

        $client->followRedirect();

        $this->assertSelectorNotExists('.kbin-sub--active');

        $crawler = $client->request('GET', '/sub');

        $this->assertEquals(0, $crawler->filter('.kbin-entry-title-domain')->count());
    }

    public function testXmlDomainSubAndUnsubController(): void
    {
        $client = static::createClient();

        $client->loginUser($this->getUserByUsername('testUser'));

        $this->createEntryFixtures();

        // subscribe
        $crawler = $client->request('GET', '/d/karab.in');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->submit(
            $crawler->filter('.kbin-domain-subscribe')->selectButton('obserwuj')->form()
        );

        $this->assertStringContainsString('{"subCount":1,"isSubscribed":true}', $client->getResponse()->getContent());

        // unsubscribe
        $crawler = $client->request('GET', '/d/karab.in');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->submit(
            $crawler->filter('.kbin-domain-subscribe')->selectButton('obserwuj')->form()
        );

        $this->assertStringContainsString('{"subCount":0,"isSubscribed":false}', $client->getResponse()->getContent());
    }
}
