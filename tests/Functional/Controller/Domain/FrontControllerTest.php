<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Domain;

use App\Tests\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    use DomainFixturesTrait;

    public function testDomainFrontPage(): void
    {
        $client = static::createClient();

        $this->createEntryFixtures();

        $crawler = $client->request('GET', '/najnowsze');

        $this->assertSelectorTextContains('.kbin-entry-title-domain', 'karab.in');

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
    public function testDomainFrontPageFilters($linkName): void
    {
        $client = static::createClient();

        $this->createEntryFixtures();

        $crawler = $client->request('GET', '/d/karab.in/'.strtolower($linkName));

        $this->assertEquals(2, $crawler->filter('.kbin-entry-title-domain')->count());
    }

    public function provider(): array
    {
        return [
            ['Ważne'],
            ['Najnowsze'],
            ['Aktywne'],
            ['Gorące'],
            ['Komentowane'],
        ];
    }
}
