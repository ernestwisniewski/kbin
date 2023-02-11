<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class FrontControllerTest extends WebTestCase
{
    public function testFrontPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry__meta', 'to acme');

        $this->assertcount(2, $crawler->filter('.kbin-entry'));
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $this->getEntryByTitle('testowa treść');

        $crawler = $client->request('GET', '/m/acme');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextNotContains('.kbin-entry__meta', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/m/acme');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-magazine', 'acme');

        $this->assertcount(1, $crawler->filter('.kbin-entry'));
    }

    public function testSubPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry__meta', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/sub');

        $this->assertcount(1, $crawler->filter('.kbin-entry'));
    }

    public function testModPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/mod');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry__meta', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/mod');

        $this->assertcount(1, $crawler->filter('.kbin-entry'));
    }

    public function testFavPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/fav');

//        $this->assertSelectorTextContains('.kbin-entry__meta', 'JaneDoe');
//        $this->assertSelectorTextContains('.kbin-entry__meta', 'to kbin');
//
//        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/fav');

        $this->assertcount(0, $crawler->filter('.kbin-entry'));
    }

    private function prepareEntries(): KernelBrowser
    {
        $client = $this->createClient();

        $this->getEntryByTitle(
            'entry test 1',
            'https://kbin.pub',
            null,
            $this->getMagazineByName('kbin', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );

        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getEntryByTitle('entry test 2');

        return $client;
    }
}
