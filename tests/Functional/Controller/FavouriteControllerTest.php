<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class FavouriteControllerTest extends WebTestCase
{
    public function testUserCanAddEntryToFavourites(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->createEntry('Lorem ipsum', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/');

        $this->assertButtonUnderline($client, $crawler, '.kbin-entry-meta-list-item');
    }

    private function assertButtonUnderline(KernelBrowser $client, Crawler $crawler, string $className): void
    {
        $crawler->filter($className)->selectButton('ulubione');

        $this->assertStringNotContainsString(
            'text-decoration-underline',
            $crawler->filter($className)->selectButton('ulubione')->outerHtml()
        );

        $client->submit(
            $crawler->filter($className)->selectButton('ulubione')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertStringContainsString(
            'text-decoration-underline',
            $crawler->filter($className)->selectButton('ulubione')->outerHtml()
        );
    }

    public function testUserCanAddEntryCommentToFavourites(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('Lorem ipsum');

        $crawler = $client->request('GET', "/m/acme/t/{$comment->entry->getId()}");

        $this->assertButtonUnderline($client, $crawler, '.kbin-comment-list-item');
    }

    public function testUserCanAddPostToFavourites()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('Lorem ipsum');

        $crawler = $client->request('GET', "/m/acme/w/{$post->getId()}");

        $this->assertButtonUnderline($client, $crawler, '.kbin-post');
    }

    public function testUserCanAddPostCommentToFavourites()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createPostComment('Lorem ipsum', $this->createPost('Lorem ipsum'));

        $crawler = $client->request('GET', "/m/acme/w/{$comment->post->getId()}");

        $this->assertButtonUnderline($client, $crawler, '.kbin-comment');
    }
}
