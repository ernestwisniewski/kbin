<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntrySingleControllerTest extends WebTestCase
{
    public function testCanSeeEntryArticle()
    {
        $client = $this->createClient();

        $this->getEntryByTitle('Lorem ipsum', null, 'dolor sit amet');

        $crawler = $client->request('GET', '/');

        $crawler = $client->click($crawler->filter('h2.kbin-entry-title a')->selectLink('Lorem ipsum')->link());

        $this->assertSelectorTextContains('h1', 'Lorem ipsum');
        $this->assertSelectorTextContains('article.kbin-entry-content', 'dolor sit amet');
        $this->assertSelectorTextContains('.kbin-entry.card', 'Wyświetlenia: 1');
    }

    public function testCanSeeEntryLink()
    {
        $client = $this->createClient();

        $this->getEntryByTitle('Lorem ipsum', 'https://youtube.com');

        $crawler = $client->request('GET', '/');

        $crawler = $client->click($crawler->filter('h2.kbin-entry-title a')->selectLink('Lorem ipsum')->link());

        $this->assertSelectorTextContains('h1 a', 'Lorem ipsum');
        $this->assertSelectorTextContains('.kbin-entry.card', 'Wyświetlenia: 1');
    }

    public function testXmlCanSeeEntryArticle()
    {
        $client = $this->createClient();

        $this->getEntryByTitle('Lorem ipsum', null, 'dolor sit amet');

        $crawler = $client->request('GET', '/');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->click($crawler->filter('h2.kbin-entry-title a')->selectLink('Lorem ipsum')->link());

        $this->assertStringContainsString('content-popup', $client->getResponse()->getContent());
        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testXmlCanSeeEntryLink()
    {
        $client = $this->createClient();

        $this->getEntryByTitle('Lorem ipsum', 'https://youtube.com');

        $crawler = $client->request('GET', '/');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->click($crawler->filter('h2.kbin-entry-title a')->selectLink('Lorem ipsum')->link());

        $this->assertStringContainsString('content-popup', $client->getResponse()->getContent());
        $this->assertStringContainsString('youtube.com', $client->getResponse()->getContent());
    }
}
