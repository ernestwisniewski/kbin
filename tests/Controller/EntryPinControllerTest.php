<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryPinControllerTest extends WebTestCase
{
    public function testModeratorCanPinAndUnpinEntry()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $this->getEntryByTitle('Sticky entry');
        $this->getEntryByTitle('Test entry');
        $this->createEntryComment('test', $this->getEntryByTitle('Test entry2'));

        $crawler = $client->request('GET', '/m/polityka/najnowsze');

        $this->assertSelectorTextContains('article.kbin-entry:nth-last-of-type(1)', 'Sticky');

        $crawler = $client->click($crawler->filter('article.kbin-entry:nth-last-of-type(1)')->selectButton('przypnij')->form([]));
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('article.kbin-entry', 'Sticky');
        $this->assertSelectorExists('article.kbin-entry .kbin-sticky');

        $crawler = $client->click($crawler->filter('article.kbin-entry')->selectButton('odepnij')->form([]));
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('article.kbin-entry:nth-last-of-type(1)', 'Sticky');
    }
}
