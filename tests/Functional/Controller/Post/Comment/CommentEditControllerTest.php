<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentEditControllerTest extends WebTestCase
{
    public function testUnauthorizedUserCannotEditPostComment(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));

        $post    = $this->createPost('example post.');
        $comment = $this->createPostComment('example comment.', $post);
        $client->request('GET', "/m/acme/w/{$post->getId()}/-/odpowiedź/{$comment->getId()}/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }
}
