<?php declare(strict_types=1);

namespace App\Tests\Controller\Domain;

use App\Tests\WebTestCase;

class CommentFrontControllerTest extends WebTestCase
{
    use DomainFixturesTrait;

    public function testDomainCommentPage(): void
    {
        $client = static::createClient();

        $this->createEntryFixtures();
        $this->createCommentFixtures();

        $client->request('GET', '/');
        $crawler = $client->request('GET', '/d/karab.in/komentarze/najnowsze');

        $this->assertSelectorTextContains('.kbin-comment-content', 'comment');

        $this->assertSelectorTextContains('.kbin-nav-navbar', '/d/karab.in');
        $this->assertEquals(2, $crawler->filter('.kbin-comment-content')->count());
    }
}
