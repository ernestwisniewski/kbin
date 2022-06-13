<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;

class EntryPinControllerTest extends WebTestCase
{
    public function testModeratorCanPinAndUnpinEntry(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('Sticky entry');
        $this->getEntryByTitle('Test entry');
        $this->createEntryComment('test', $this->getEntryByTitle('Test entry2'));

        $crawler = $client->request('GET', '/m/acme/najnowsze');

        $this->assertSelectorTextContains('article.kbin-entry:nth-last-of-type(1)', 'Sticky');

        $client->click($crawler->filter('article.kbin-entry:nth-last-of-type(1)')->selectButton('przypnij')->form([]));
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('article.kbin-entry', 'Sticky');
        $this->assertSelectorExists('article.kbin-entry .kbin-sticky');

        $client->click($crawler->filter('article.kbin-entry')->selectButton('odepnij')->form([]));
        $client->followRedirect();

        $this->assertSelectorTextContains('article.kbin-entry:nth-last-of-type(1)', 'Sticky');
    }
}
