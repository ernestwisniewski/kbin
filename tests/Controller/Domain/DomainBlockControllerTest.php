<?php declare(strict_types=1);

namespace App\Tests\Controller\Domain;

use App\Tests\WebTestCase;

class DomainBlockControllerTest extends WebTestCase
{
    use DomainFixturesTrait;

    public function testDomainBlockAndUnblockController()
    {
        $client = static::createClient();

        $client->loginUser($this->getUserByUsername('testUser'));

        $this->createEntryFixtures();
        $this->createCommentFixtures();

        // block
        $crawler = $client->request('GET', '/d/karab.in');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $client->followRedirect();

        $this->assertSelectorExists('.kbin-block--active');

        $crawler = $client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter('.kbin-entry-title-domain')->count());

        // unblock
        $crawler = $client->request('GET', '/d/karab.in');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $client->followRedirect();

        $this->assertSelectorNotExists('.kbin-block--active');

        $crawler = $client->request('GET', '/');

        $this->assertEquals(3, $crawler->filter('.kbin-entry-title-domain')->count());
    }

    public function testDomainBlockCommentsController()
    {
        $client = static::createClient();

        $client->loginUser($this->getUserByUsername('testUser'));

        $this->createEntryFixtures();

        $this->createEntryComment('comment1', $this->getEntryByTitle('karabin1'));
        $this->createEntryComment('comment2', $this->getEntryByTitle('karabin2'));
        $this->createEntryComment('comment3', $this->getEntryByTitle('google'));

        //block
        $crawler = $client->request('GET', '/d/karab.in');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $client->followRedirect();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter('.kbin-entry-title-domain')->count());

        // unblock
        $crawler = $client->request('GET', '/d/karab.in');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $client->followRedirect();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(3, $crawler->filter('.kbin-entry-title-domain')->count());
    }

    public function testXmlDomainBlockAndUnblockController() {
        $client = static::createClient();

        $client->loginUser($this->getUserByUsername('testUser'));

        $this->createEntryFixtures();

        // block
        $crawler = $client->request('GET', '/d/karab.in');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $this->assertStringContainsString('{"isBlocked":true}', $client->getResponse()->getContent());

        // unblock
        $crawler = $client->request('GET', '/d/karab.in');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $this->assertStringContainsString('{"isBlocked":false}', $client->getResponse()->getContent());
    }
}
