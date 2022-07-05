<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class CommentCreateControllerTest extends WebTestCase
{
    public function testCanCreatePostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $post = $this->createPost('example post');

        $crawler = $client->request('GET', '/m/acme/wpisy');
        $crawler = $client->click($crawler->filter('.kbin-post-meta')->selectLink('odpowiedz')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'post_comment[body]' => 'testowy komentarz.',
                ]
            )
        );

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-comment-main', 'testowy komentarz.');
    }
}
