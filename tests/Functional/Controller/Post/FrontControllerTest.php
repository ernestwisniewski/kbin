<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class FrontControllerTest extends WebTestCase
{
    public function testFrontPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/microblog');

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-post header', 'to acme');

        $this->assertcount(2, $crawler->filter('.kbin-entry'));
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/m/acme/microblog');

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextNotContains('.kbin-post header', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/m/acme');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-magazine', 'acme');

        $this->assertcount(1, $crawler->filter('.kbin-post'));
    }

    public function testSubPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-post header', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/sub');

        $this->assertcount(1, $crawler->filter('.kbin-post'));
    }

    public function testModPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/mod');

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-post header', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/mod');

        $this->assertcount(1, $crawler->filter('.kbin-post'));
    }

    public function testFavPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/fav');

//        $this->assertSelectorTextContains('.kbin-entry__meta', 'JaneDoe');
//        $this->assertSelectorTextContains('.kbin-entry__meta', 'to kbin');
//
        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/fav');

        $this->assertcount(0, $crawler->filter('.kbin-post'));
    }

    private function prepareEntries(): KernelBrowser
    {
        $client = $this->createClient();

        $this->createPost(
            'post test 1',
            $this->getMagazineByName('kbin', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );

        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->createPost('post test 2');

        return $client;
    }
}
