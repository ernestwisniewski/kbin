<?php declare(strict_types=1);

namespace App\Tests\Controller\Domain;

use App\Tests\WebTestCase;

class CommentFrontControllerTest extends WebTestCase
{
    use DomainFixturesTrait;

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
}
