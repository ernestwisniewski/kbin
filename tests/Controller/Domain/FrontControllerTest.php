<?php declare(strict_types=1);

namespace App\Tests\Controller\Domain;

use App\Tests\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    use DomainFixturesTrait;

    public function testDomainFrontPage()
    {
        $client = static::createClient();

        $this->createFixtures();

        $crawler = $client->request('GET', '/najnowsze');

        $this->assertSelectorTextContains('.kbin-entry-title-domain', 'google.pl');

        $crawler = $client->click($crawler->filter('.kbin-entry-title-domain')->selectLink('karab.in')->link());

        $this->assertSelectorTextContains('.kbin-nav-navbar', '/d/karab.in');
        $this->assertEquals(2, $crawler->filter('.kbin-entry-title-domain')->count());

        $crawler = $client->request('GET', '/d/google.pl/najnowsze');

        $this->assertSelectorTextContains('.kbin-nav-navbar', '/d/google.pl');
        $this->assertEquals(1, $crawler->filter('.kbin-entry-title-domain')->count());
    }

    /**
     * @dataProvider provider
     */
    public function testDomainFrontPageFilters($linkName)
    {
        $client = static::createClient();

        $this->createFixtures();

        $crawler = $client->request('GET', '/d/karab.in/'.strtolower($linkName));

        $this->assertEquals(2, $crawler->filter('.kbin-entry-title-domain')->count());
    }

    public function testDomainCommentPage()
    {
        $client = static::createClient();

        $this->createFixtures();

        $this->createEntryComment('comment1', $this->getEntryByTitle('karabin1'));
        $this->createEntryComment('comment2', $this->getEntryByTitle('karabin2'));
        $this->createEntryComment('comment3', $this->getEntryByTitle('google'));

        $crawler = $client->request('GET', '/d/karab.in/komentarze/najnowsze');

        $this->assertSelectorTextContains('.kbin-comment-content', 'comment2');

        $this->assertSelectorTextContains('.kbin-nav-navbar', '/d/karab.in');
        $this->assertEquals(2, $crawler->filter('.kbin-comment-content')->count());
    }

    public function provider(): array
    {
        return [
            ['Ważne'],
            ['Najnowsze'],
            ['Aktywne'],
            ['Wschodzące'],
            ['Komentowane'],
        ];
    }
}
