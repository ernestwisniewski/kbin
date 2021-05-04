<?php declare(strict_types=1);

namespace App\Tests\Controller\Post;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostCommentControllerTest extends WebTestCase
{
    public function testCanCreateArticle()
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

    public function testCanEditLink()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $post = $this->createPost('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/wpisy");
        $crawler = $client->click($crawler->filter('.kbin-post-meta')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'post[body]' => 'zmieniona treść',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-post-main', 'zmieniona treść');
    }

    public function testUnauthorizedUserCannotEditEntryMagazine()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser2'));

        $post    = $this->createPost('przykladowa post.');
        $comment = $this->createPostComment('przykłądowy komentarz.', $post);
        $crawler = $client->request('GET', "/m/polityka/w/{$post->getId()}/-/komentarz/{$comment->getId()}/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }
}
