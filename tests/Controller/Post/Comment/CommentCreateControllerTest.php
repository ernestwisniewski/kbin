<?php declare(strict_types=1);

namespace App\Tests\Controller\Post\Comment;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentCreateControllerTest extends WebTestCase
{
    public function testCanCreatePostComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $post = $this->createPost('przykładowy post');

        $crawler = $client->request('GET', '/m/polityka/wpisy');
        $crawler = $client->click($crawler->filter('.kbin-post-meta')->selectLink('odpowiedz')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'post_comment[body]' => 'testowy komentarz.',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-comment-main', 'testowy komentarz.');
    }
}
