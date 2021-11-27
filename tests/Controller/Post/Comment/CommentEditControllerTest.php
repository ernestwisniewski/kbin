<?php declare(strict_types=1);

namespace App\Tests\Controller\Post\Comment;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentEditControllerTest extends WebTestCase
{
    public function testUnauthorizedUserCannotEditPostComment()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser2'));

        $post    = $this->createPost('przykladowa post.');
        $comment = $this->createPostComment('przykłądowy komentarz.', $post);
        $crawler = $client->request('GET', "/m/polityka/w/{$post->getId()}/-/odpowiedź/{$comment->getId()}/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }
}
