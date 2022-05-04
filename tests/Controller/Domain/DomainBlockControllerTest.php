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

        $crawler = $client->request('GET', '/d/karab.in');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $client->followRedirect();

        $this->assertSelectorExists('.kbin-block--active');

        $crawler = $client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter('.kbin-entry-title-domain')->count());

        $crawler = $client->request('GET', '/d/karab.in');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

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

        $crawler = $client->request('GET', '/d/karab.in');

        $client->submit(
            $crawler->filter('.kbin-domains .kbin-sub')->selectButton('')->form()
        );

        $client->followRedirect();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter('.kbin-entry-title-domain')->count());
    }
}
